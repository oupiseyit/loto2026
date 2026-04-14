# Lotto — Roles & Permissions

The app has **3 roles**: `admin`, `master`, and `staff`.  
Stored as `role` enum(`admin`,`master`,`staff`) on the `users` table.

## Role Definitions

| Role | Description |
|---|---|
| `admin` | Full system access + reports + all data management |
| `master` | Can create/manage staff accounts + view reports (no system settings) |
| `staff` | Can only enter bets on the Home page — own data only |

## Access Matrix

| Screen | admin | master | staff |
|---|---|---|---|
| Login | ✅ | ✅ | ✅ |
| Home (Betting) | ✅ View all bets | ✅ View own bets | ✅ Add bets only |
| Record | ✅ All records | ✅ Own staff records | ✅ Own records only |
| Result | ✅ Read + Enter/Edit | ✅ Read-only | ✅ Read-only |
| Report | ✅ Full report (all staff) | ✅ Report (own staff) | ❌ No access |
| Setting | ✅ Full (incl. Commission) | ❌ No access | ✅ Printer only |
| Account | ✅ All staff sales + full user mgmt | ✅ Sales + create/manage staff | ✅ Own sales only |

## Per-Screen Detail

### Home (Betting)
| Action | admin | master | staff |
|---|---|---|---|
| View all bets | ✅ | ❌ | ❌ |
| View own bets | ✅ | ✅ | ✅ |
| Add / submit bets | ✅ | ✅ | ✅ |
| Delete any bet | ✅ | ❌ | ❌ |
| Delete own bet | ✅ | ✅ | ✅ |

### Report (new screen)
| Action | admin | master | staff |
|---|---|---|---|
| Summary report (all staff) | ✅ | ❌ | ❌ |
| Summary report (own staff) | ✅ | ✅ | ❌ |
| Export / print report | ✅ | ✅ | ❌ |

### Account / User Management
| Action | admin | master | staff |
|---|---|---|---|
| View own sales | ✅ | ✅ | ✅ |
| View all staff sales | ✅ | ✅ (own staff) | ❌ |
| Change own password | ✅ | ✅ | ✅ |
| Create staff account | ✅ | ✅ | ❌ |
| Edit staff account | ✅ | ✅ (own staff) | ❌ |
| Delete staff account | ✅ | ❌ | ❌ |
| Create master account | ✅ | ❌ | ❌ |
| Manage commission | ✅ | ❌ | ❌ |

---

## Laravel Implementation

### Migration
```php
// users table role column
$table->enum('role', ['admin', 'master', 'staff'])->default('staff');
```

### Middleware (`app/Http/Middleware/RoleMiddleware.php`)
```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!in_array($request->user()?->role, $roles)) {
        return $request->expectsJson()
            ? response()->json(['success' => false, 'message' => 'Unauthorized'], 403)
            : abort(403);
    }
    return $next($request);
}
```

**Register in** `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => RoleMiddleware::class]);
})
```

### Route Groups
```php
// All roles
Route::middleware(['auth', 'role:admin,master,staff'])->group(...);

// Admin + master (report, user management)
Route::middleware(['auth', 'role:admin,master'])->group(...);

// Admin only (system settings, commission, delete users)
Route::middleware(['auth', 'role:admin'])->group(...);
```

### Eloquent Scoping
```php
$user = auth()->user();

// Home: staff sees own bets only; master sees own; admin sees all
$query->when(
    in_array($user->role, ['staff', 'master']),
    fn($q) => $q->where('user_id', $user->id)
);

// Report: master sees only their own staff's data
$query->when(
    $user->role === 'master',
    fn($q) => $q->whereHas('user', fn($u) => $u->where('created_by', $user->id))
);

// User management: master can only manage staff they created
$query->when(
    $user->role === 'master',
    fn($q) => $q->where('role', 'staff')->where('created_by', $user->id)
);
```

> Add `created_by` (foreign key → `users.id`) column to `users` table to track which master created each staff.

---

## React Implementation

### Role helpers
```jsx
// resources/js/utils/roles.js
export const ROLES = { ADMIN: 'admin', MASTER: 'master', STAFF: 'staff' };

export function useRole() {
    const { auth } = usePage().props;
    const role = auth.user.role;
    return {
        role,
        isAdmin:  role === ROLES.ADMIN,
        isMaster: role === ROLES.MASTER,
        isStaff:  role === ROLES.STAFF,
        isAdminOrMaster: role === ROLES.ADMIN || role === ROLES.MASTER,
    };
}
```

### Guard component
```jsx
// resources/js/Components/RequireRole.jsx
export default function RequireRole({ roles, children, fallback = null }) {
    const { auth } = usePage().props;
    const allowed = Array.isArray(roles) ? roles : [roles];
    return allowed.includes(auth.user.role) ? children : fallback;
}

// Usage
<RequireRole roles={['admin', 'master']}>
    <ReportLink />
</RequireRole>

<RequireRole roles="admin">
    <CommissionSettings />
</RequireRole>
```

### Nav bar — role-based tabs
```jsx
const { isAdmin, isAdminOrMaster } = useRole();

// Show Report tab only for admin and master
{isAdminOrMaster && <NavTab href={route('report')} label="Report" />}

// Show Setting tab for admin and staff (not master)
{!isMaster && <NavTab href={route('setting')} label="Setting" />}
```

**Route protection:** Always handle in Laravel middleware — React hides UI only, never trusts role client-side for access control.

---

## API Role Middleware (Mobile)

```php
// All roles
->middleware(['auth:sanctum', 'role:admin,master,staff'])

// Admin + master
->middleware(['auth:sanctum', 'role:admin,master'])

// Admin only
->middleware(['auth:sanctum', 'role:admin'])
```

Login response includes role so mobile app can show/hide features:
```json
{ "data": { "token": "...", "user": { "role": "master" } } }
```
