# Lotto — Swagger / OpenAPI Documentation

Generate Swagger (OpenAPI 3.0) annotations for the **HT Lotto App** API using **L5-Swagger** (darkaonline/l5-swagger).

## How to use
```
/lotto-swagger <screen>
```

| Argument | Values | Default |
|---|---|---|
| `screen` | `setup` `auth` `home` `record` `result` `report` `setting` `account` `all` | required |

**Examples:**
```
/lotto-swagger setup        ← install + configure L5-Swagger
/lotto-swagger auth         ← annotate auth endpoints
/lotto-swagger all          ← annotate all screens
```

---

## Reference Files
- `lotto-api.md` — all endpoint definitions, request/response shapes
- `lotto-roles.md` — role access per endpoint (admin / master / staff)

---

## Setup (`/lotto-swagger setup`)

### 1. Install L5-Swagger
```bash
docker compose exec app composer require darkaonline/l5-swagger
docker compose exec app php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

### 2. `config/l5-swagger.php` — key settings
```php
'default' => 'default',
'documentations' => [
    'default' => [
        'api' => [
            'title' => 'HT Lotto API',
        ],
        'routes' => [
            'api'  => 'api/documentation',      // Swagger UI at /api/documentation
            'docs' => 'api/docs',               // Raw JSON at /api/docs
        ],
        'paths' => [
            'docs'          => storage_path('api-docs'),
            'annotations'   => [
                base_path('app/Http/Controllers/Api'),
                base_path('app/Http/Controllers/Api/V1'),
                base_path('app/Virtual'),       // virtual schemas
            ],
        ],
    ],
],
'defaults' => [
    'securityDefinitions' => [
        'securitySchemes' => [
            'bearerAuth' => [
                'type'         => 'http',
                'scheme'       => 'bearer',
                'bearerFormat' => 'sanctum',
            ],
        ],
    ],
    'security' => [['bearerAuth' => []]],       // apply globally
],
```

### 3. Base API annotation (`app/Http/Controllers/Api/V1/Controller.php`)
```php
/**
 * @OA\Info(
 *     title="HT Lotto API",
 *     version="1.0.0",
 *     description="REST API for HT ភ្នាក់ Lottery Agent App. Supports 3 roles: admin, master, staff.",
 *     @OA\Contact(email="admin@yourdomain.com")
 * )
 *
 * @OA\Server(url="/api/v1", description="API v1")
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum"
 * )
 *
 * @OA\Tag(name="Auth",    description="Authentication endpoints")
 * @OA\Tag(name="Bets",    description="Home / Betting — all roles")
 * @OA\Tag(name="Records", description="Bet records — all roles")
 * @OA\Tag(name="Results", description="Lottery results")
 * @OA\Tag(name="Reports", description="Reports — admin and master only")
 * @OA\Tag(name="Settings",description="Printer settings")
 * @OA\Tag(name="Account", description="Account and user management")
 */
abstract class Controller extends BaseController {}
```

### 4. Generate docs
```bash
docker compose exec app php artisan l5-swagger:generate
```

### 5. View Swagger UI
```
http://localhost:8080/api/documentation
```

### 6. Add to `.gitignore`
```
/storage/api-docs/
```

### 7. Auto-generate on deploy
```bash
# Add to scripts/deploy.sh after migrate:
docker compose -f docker-compose.prod.yml exec -T app php artisan l5-swagger:generate
```

---

## Virtual Schema Classes (`app/Virtual/`)

These are annotation-only classes (no logic) used to define reusable schemas.

### Standard response wrappers
```php
// app/Virtual/Responses/SuccessResponse.php
/**
 * @OA\Schema(schema="SuccessResponse",
 *   @OA\Property(property="success", type="boolean", example=true),
 *   @OA\Property(property="message", type="string",  example="OK")
 * )
 */
class SuccessResponse {}

// app/Virtual/Responses/ErrorResponse.php
/**
 * @OA\Schema(schema="ErrorResponse",
 *   @OA\Property(property="success", type="boolean", example=false),
 *   @OA\Property(property="message", type="string",  example="Unauthorized"),
 *   @OA\Property(property="errors",  type="object")
 * )
 */
class ErrorResponse {}
```

### User schema
```php
// app/Virtual/Schemas/UserSchema.php
/**
 * @OA\Schema(schema="User",
 *   @OA\Property(property="id",      type="integer", example=1),
 *   @OA\Property(property="name",    type="string",  example="បង ម៉េន"),
 *   @OA\Property(property="role",    type="string",  enum={"admin","master","staff"}),
 *   @OA\Property(property="balance", type="integer", example=1108898)
 * )
 */
class UserSchema {}
```

### Bet schema
```php
// app/Virtual/Schemas/BetSchema.php
/**
 * @OA\Schema(schema="Bet",
 *   @OA\Property(property="id",       type="integer", example=1),
 *   @OA\Property(property="session",  type="string",  enum={"morning","noon","evening"}),
 *   @OA\Property(property="bet_type", type="string",  enum={"ABCD","LO"}),
 *   @OA\Property(property="letter",   type="string",  enum={"A","B","C","D","F","I","N"}),
 *   @OA\Property(property="position", type="string",  enum={"X","W","H","W*"}),
 *   @OA\Property(property="number",   type="string",  example="25"),
 *   @OA\Property(property="amount",   type="integer", example=5000)
 * )
 */
class BetSchema {}
```

### Report schema
```php
// app/Virtual/Schemas/ReportSchema.php
/**
 * @OA\Schema(schema="ReportSummary",
 *   @OA\Property(property="total_bets",   type="integer", example=120),
 *   @OA\Property(property="total_amount", type="integer", example=450000),
 *   @OA\Property(property="total_win",    type="integer", example=80000),
 *   @OA\Property(property="net",          type="integer", example=370000),
 *   @OA\Property(property="staff_count",  type="integer", example=5),
 *   @OA\Property(property="by_session",   type="object",
 *     @OA\Property(property="morning", type="object",
 *       @OA\Property(property="bets",   type="integer", example=40),
 *       @OA\Property(property="amount", type="integer", example=150000)
 *     )
 *   )
 * )
 */
class ReportSchema {}
```

---

## Auth Annotations

```php
// app/Http/Controllers/Api/V1/AuthApiController.php

/**
 * @OA\Post(
 *     path="/login",
 *     tags={"Auth"},
 *     summary="Login and get Sanctum token",
 *     security={},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(required={"username","password"},
 *             @OA\Property(property="username", type="string", example="staff01"),
 *             @OA\Property(property="password", type="string", example="password")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="token", type="string", example="1|abc123"),
 *                 @OA\Property(property="user", ref="#/components/schemas/User")
 *             ),
 *             @OA\Property(property="message", type="string", example="Login successful")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Invalid credentials",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function login(Request $request): JsonResponse {}

/**
 * @OA\Post(
 *     path="/logout",
 *     tags={"Auth"},
 *     summary="Revoke current token",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Logged out",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function logout(): JsonResponse {}

/**
 * @OA\Get(
 *     path="/me",
 *     tags={"Auth"},
 *     summary="Get authenticated user",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Current user",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/User")
 *         )
 *     )
 * )
 */
public function me(): JsonResponse {}
```

---

## Home / Betting Annotations

```php
// app/Http/Controllers/Api/V1/BetController.php

/**
 * @OA\Get(
 *     path="/bets",
 *     tags={"Bets"},
 *     summary="List today's bets (staff: own only; admin: all)",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="session", in="query", required=false,
 *         @OA\Schema(type="string", enum={"morning","noon","evening"})
 *     ),
 *     @OA\Response(response=200, description="Bet list",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(ref="#/components/schemas/Bet")
 *             )
 *         )
 *     )
 * )
 */
public function index(): JsonResponse {}

/**
 * @OA\Post(
 *     path="/bets",
 *     tags={"Bets"},
 *     summary="Place a new bet",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(required={"session","bet_type","letter","position","number","amount"},
 *             @OA\Property(property="session",  type="string", enum={"morning","noon","evening"}),
 *             @OA\Property(property="bet_type", type="string", enum={"ABCD","LO"}),
 *             @OA\Property(property="letter",   type="string", enum={"A","B","C","D","F","I","N"}),
 *             @OA\Property(property="position", type="string", enum={"X","W","H","W*"}),
 *             @OA\Property(property="number",   type="string", example="25"),
 *             @OA\Property(property="amount",   type="integer", example=5000)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Bet created",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/Bet")
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function store(BetRequest $request): JsonResponse {}

/**
 * @OA\Delete(
 *     path="/bets/{id}",
 *     tags={"Bets"},
 *     summary="Delete a bet (own only; admin: any)",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Deleted",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(response=403, description="Forbidden",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function destroy(int $id): JsonResponse {}

/**
 * @OA\Post(
 *     path="/bets/submit",
 *     tags={"Bets"},
 *     summary="Submit bet list for the session",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="session", type="string", enum={"morning","noon","evening"})
 *         )
 *     ),
 *     @OA\Response(response=200, description="Submitted",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     )
 * )
 */
public function submit(Request $request): JsonResponse {}
```

---

## Report Annotations _(admin + master)_

```php
// app/Http/Controllers/Api/V1/ReportController.php

/**
 * @OA\Get(
 *     path="/reports/summary",
 *     tags={"Reports"},
 *     summary="Summary report — admin: all staff; master: own staff only",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="from",     in="query", @OA\Schema(type="string", format="date", example="2025-07-01")),
 *     @OA\Parameter(name="to",       in="query", @OA\Schema(type="string", format="date", example="2025-07-06")),
 *     @OA\Parameter(name="staff_id", in="query", @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Report summary",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/ReportSummary")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Staff forbidden",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function summary(Request $request): JsonResponse {}

/**
 * @OA\Get(
 *     path="/reports/export",
 *     tags={"Reports"},
 *     summary="Export report as PDF or CSV",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="format", in="query", required=true,
 *         @OA\Schema(type="string", enum={"pdf","csv"})
 *     ),
 *     @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="to",   in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File download",
 *         @OA\MediaType(mediaType="application/pdf"),
 *         @OA\MediaType(mediaType="text/csv")
 *     )
 * )
 */
public function export(Request $request): StreamedResponse {}
```

---

## Account / User Management Annotations

```php
// app/Http/Controllers/Api/V1/UserController.php

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"Account"},
 *     summary="Create staff account — master: own staff; admin: any",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(required={"name","username","password"},
 *             @OA\Property(property="name",     type="string", example="New Staff"),
 *             @OA\Property(property="username", type="string", example="staff01"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Staff created",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/User")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Staff role cannot create users",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function store(UserRequest $request): JsonResponse {}

/**
 * @OA\Post(
 *     path="/users/master",
 *     tags={"Account"},
 *     summary="Create master account — admin only",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(required={"name","username","password"},
 *             @OA\Property(property="name",     type="string"),
 *             @OA\Property(property="username", type="string"),
 *             @OA\Property(property="password", type="string")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Master created",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/User")
 *         )
 *     ),
 *     @OA\Response(response=403, description="Admin only",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function storeMaster(UserRequest $request): JsonResponse {}

/**
 * @OA\Put(
 *     path="/account/password",
 *     tags={"Account"},
 *     summary="Change own password — all roles",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true,
 *         @OA\JsonContent(required={"current_password","password","password_confirmation"},
 *             @OA\Property(property="current_password",      type="string"),
 *             @OA\Property(property="password",              type="string"),
 *             @OA\Property(property="password_confirmation", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Password updated",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *     @OA\Response(response=422, description="Wrong current password",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
public function changePassword(PasswordRequest $request): JsonResponse {}
```

---

## Code Generation Instructions

When generating Swagger annotations for a screen:

1. **Add `@OA\` annotations** directly above the controller method — no separate file.
2. **Always reference schemas** via `ref="#/components/schemas/..."` — never inline objects for reusable shapes.
3. **Include all HTTP responses:** 200/201 (success), 401 (unauthenticated), 403 (wrong role), 422 (validation), 404 (not found).
4. **Add role note** in the `summary` field: e.g. `"admin only"`, `"master: own staff only"`, `"all roles"`.
5. **Mark public endpoints** with `security={}` to override the global bearerAuth.
6. **Enums** for `session`, `bet_type`, `letter`, `position`, `role` — always use `enum={}` in schema.
7. **Generate virtual schema classes** in `app/Virtual/Schemas/` for any new response shape.
8. After generating, run:
   ```bash
   docker compose exec app php artisan l5-swagger:generate
   ```
   Then open `http://localhost:8080/api/documentation` to verify UI loads.

---

## Argument: `$ARGUMENTS`

Generate Swagger annotations for the screen specified.

**Parsing rules:**
- **Word 1** → screen: `setup` `auth` `home` `record` `result` `report` `setting` `account` `all`

**Output order:**
1. If `setup`: install commands + `config/l5-swagger.php` changes + base `Controller.php` annotation
2. Virtual schema classes (`app/Virtual/`) for any new response shapes
3. Annotated controller methods for each endpoint of the screen
4. Run command: `php artisan l5-swagger:generate`
5. Swagger UI URL to verify
