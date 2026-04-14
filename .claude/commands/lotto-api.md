# Lotto — Mobile API Specification

All endpoints under `/api/v1/`. Auth via **Laravel Sanctum Bearer token**.

**3 roles:** `admin` | `master` | `staff`

## Standard Response Format
```json
// Success
{ "success": true, "data": {}, "message": "OK" }

// Validation error (422)
{ "success": false, "message": "Validation failed", "errors": { "field": ["msg"] } }

// Unauthorized (403)
{ "success": false, "message": "Unauthorized" }

// Not found (404)
{ "success": false, "message": "Not found" }
```

---

## Auth Endpoints (Public)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/v1/login` | Get Sanctum token |
| POST | `/api/v1/logout` | Revoke token (auth required) |
| GET | `/api/v1/me` | Current user info (auth required) |

**Login request:**
```json
{ "username": "string", "password": "string" }
```
**Login response:**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123...",
    "user": { "id": 1, "name": "បង ម៉េន", "role": "staff", "balance": 1108898 }
  },
  "message": "Login successful"
}
```

---

## Home / Betting Endpoints

| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/api/v1/bets` | admin, master, staff | Today's bets (staff/master: own only; admin: all) |
| POST | `/api/v1/bets` | admin, master, staff | Place a new bet |
| DELETE | `/api/v1/bets/{id}` | admin, master, staff | Remove own bet (admin: any) |
| POST | `/api/v1/bets/submit` | admin, master, staff | Submit bet list for session |
| GET | `/api/v1/sessions` | admin, master, staff | Available sessions |

**POST `/api/v1/bets` body:**
```json
{
  "session": "morning|noon|evening",
  "bet_type": "ABCD|LO",
  "letter": "A|B|C|D|F|I|N",
  "position": "X|W|H|W*",
  "number": "string",
  "amount": 5000
}
```

---

## Report Endpoints _(admin + master only)_

| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/api/v1/reports/summary` | admin, master | Summary report (admin: all staff; master: own staff) |
| GET | `/api/v1/reports/daily` | admin, master | Daily sales breakdown by date |
| GET | `/api/v1/reports/staff` | admin, master | Per-staff performance report |
| GET | `/api/v1/reports/export` | admin, master | Export report as PDF/CSV |

**Query params:** `?from=2025-07-01&to=2025-07-06&staff_id=3`

**Summary response:**
```json
{
  "total_bets": 120,
  "total_amount": 450000,
  "total_win": 80000,
  "net": 370000,
  "staff_count": 5,
  "by_session": {
    "morning": { "bets": 40, "amount": 150000 },
    "noon":    { "bets": 50, "amount": 180000 },
    "evening": { "bets": 30, "amount": 120000 }
  }
}
```

---

## Record Endpoints

| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/api/v1/records` | admin, staff | Bet records (staff: own only) |
| GET | `/api/v1/records/winning` | admin, staff | Winning records only |

**Query params:** `?date=2025-07-06&session=morning`

**Record row response:**
```json
{
  "id": 1,
  "invoice_no": "INV-0001",
  "quantity": 3,
  "bet_amount": 15000,
  "price": 12000,
  "balance": 3000,
  "win_lose": 3000
}
```

---

## Result Endpoints

| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/api/v1/results` | admin, staff | Results by date + session |
| POST | `/api/v1/results` | **admin only** | Enter new result |
| PUT | `/api/v1/results/{id}` | **admin only** | Edit a result |

**Query params:** `?date=2025-07-06&session=morning`

**Result row response:**
```json
{ "position": "A", "number": "25" }
```

---

## Setting Endpoints

| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/api/v1/settings` | admin, staff | Get printer settings |
| PUT | `/api/v1/settings` | admin, staff | Update printer settings |
| PUT | `/api/v1/settings/commission` | **admin only** | Update commission |

**Settings response:**
```json
{
  "bluetooth": true,
  "device": "58MM",
  "logo": "logo",
  "commission": "default"
}
```

---

## Account Endpoints

| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/api/v1/account/sales` | admin, staff | Today's sales (staff: own) |
| GET | `/api/v1/account/sales/detail` | admin, staff | Sales breakdown |
| PUT | `/api/v1/account/password` | admin, staff | Change own password |
| GET | `/api/v1/admin/users` | **admin only** | List all staff |
| POST | `/api/v1/admin/users` | **admin only** | Create staff account |
| PUT | `/api/v1/admin/users/{id}` | **admin only** | Update staff account |

---

## `routes/api.php` Structure
```php
Route::prefix('v1')->group(function () {

    // Public
    Route::post('/login', [AuthApiController::class, 'login']);

    // All roles (admin, master, staff)
    Route::middleware(['auth:sanctum', 'role:admin,master,staff'])->group(function () {
        Route::post('/logout',              [AuthApiController::class, 'logout']);
        Route::get('/me',                   [AuthApiController::class, 'me']);
        Route::apiResource('bets',          BetController::class)->except(['show', 'update']);
        Route::post('/bets/submit',         [BetController::class, 'submit']);
        Route::get('/sessions',             [SessionController::class, 'index']);
        Route::get('/records',              [RecordController::class, 'index']);
        Route::get('/records/winning',      [RecordController::class, 'winning']);
        Route::get('/results',              [ResultController::class, 'index']);
        Route::get('/settings',             [SettingController::class, 'show']);
        Route::put('/settings',             [SettingController::class, 'update']);
        Route::get('/account/sales',        [AccountController::class, 'sales']);
        Route::get('/account/sales/detail', [AccountController::class, 'salesDetail']);
        Route::put('/account/password',     [AccountController::class, 'changePassword']);
    });

    // Admin + master (report, user management)
    Route::middleware(['auth:sanctum', 'role:admin,master'])->group(function () {
        Route::get('/reports/summary',      [ReportController::class, 'summary']);
        Route::get('/reports/daily',        [ReportController::class, 'daily']);
        Route::get('/reports/staff',        [ReportController::class, 'staff']);
        Route::get('/reports/export',       [ReportController::class, 'export']);
        Route::get('/users',                [UserController::class, 'index']);
        Route::post('/users',               [UserController::class, 'store']);    // create staff
        Route::put('/users/{id}',           [UserController::class, 'update']);   // master: own staff only
    });

    // Admin only
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/results',                [ResultController::class, 'store']);
        Route::put('/results/{id}',            [ResultController::class, 'update']);
        Route::put('/settings/commission',     [SettingController::class, 'commission']);
        Route::delete('/users/{id}',           [UserController::class, 'destroy']);
        Route::post('/users/master',           [UserController::class, 'storeMaster']); // create master
    });

});
```

## Controller Pattern (API)
```php
// app/Http/Controllers/Api/V1/<Screen>Controller.php
public function index(Request $request): JsonResponse
{
    $query = Bet::query();

    // Scope for staff
    if (auth()->user()->role === 'staff') {
        $query->where('user_id', auth()->id());
    }

    $data = $query->whereDate('created_at', today())->get();

    return response()->json([
        'success' => true,
        'data'    => BetResource::collection($data),
        'message' => 'OK',
    ]);
}
```
