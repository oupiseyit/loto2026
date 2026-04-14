# Lotto Screen Generator

Generate a screen for the **HT ភ្នាក់ Lotto App** (Cambodian lottery agent app).

## How to use
```
/lotto-screen <screen> [theme]
```

| Argument | Values | Default |
|---|---|---|
| `screen` | `login` `home` `record` `result` `report` `setting` `account` | required |
| `theme` | see `lotto-design.md` for full list | `gold/crimson` |

**Examples:**
```
/lotto-screen login gold/ruby
/lotto-screen home amber/scarlet
/lotto-screen record
```

---

## Reference Files

| File | Contents |
|---|---|
| `lotto-design.md` | Color schemes, token roles, typography, nav bar, button/table styles |
| `lotto-screens.md` | Layout + component spec for all 7 screens |
| `lotto-roles.md` | admin/master/staff permissions, middleware, Eloquent scoping, React guards |
| `lotto-stack.md` | Laravel + Inertia (web) + Sanctum API (mobile) architecture & file structure |
| `lotto-docker.md` | Docker Compose, Dockerfile, Nginx config, `.env`, common commands |
| `lotto-api.md` | All `/api/v1/` endpoints, request/response shapes, route structure |
| `lotto-swagger.md` | Swagger/OpenAPI annotations (L5-Swagger), virtual schemas, UI setup |
| `lotto-test.md` | PHPUnit feature tests — web (Inertia) + API, all 3 roles |
| `lotto-db.md` | Database schema — tables, columns, indexes, Eloquent scoping, seeder |

---

## App Overview

**App:** HT ភ្នាក់ (HT Lottery Agent)  
**Stack:** Laravel 11 + Inertia.js + React 18 + Tailwind CSS + Vite  
**Mobile API:** Laravel Sanctum Bearer token — `routes/api.php`  
**Roles:** `admin` (full access + reports) | `master` (create staff + reports) | `staff` (home/betting only)  
**Infrastructure:** Docker (PHP 8.3-FPM + Nginx + MySQL 8.0 + Node 20)

---

## Argument: `$ARGUMENTS`

Read the reference files above, then generate all output files for the requested screen.

### Output order
1. Header comment: `// Screen: <name> | Theme: <theme> | Stack: Laravel+Inertia+React+API+Docker+MySQL`
2. `resources/js/theme/colors.js` — hex constants for the chosen theme (from `lotto-design.md`)
3. `routes/web.php` — Inertia route with role middleware (from `lotto-roles.md`)
4. `routes/api.php` — `/api/v1/` routes for mobile (from `lotto-api.md`)
5. `app/Http/Controllers/<Screen>Controller.php` — `Inertia::render()` web controller
6. `app/Http/Controllers/Api/V1/<Screen>Controller.php` — JSON API controller for mobile
7. `app/Http/Requests/<Screen>Request.php` — shared validation (web + API)
8. `app/Http/Resources/<Screen>Resource.php` — API JSON transformer
9. `resources/js/Pages/<Screen>.jsx` — React page (Inertia props + role-aware UI)
10. `database/migrations/<ts>_create_<table>_table.php` — only if screen needs a new table
11. Docker files — only if user requests setup or this is the first screen

### Code rules
- Web controller: always `Inertia::render()` — never JSON
- API controller: always `response()->json()` — never Inertia
- React: use `useForm()` + `<Link>` from `@inertiajs/react` — no Axios
- Theme: import hex from `colors.js`, use Tailwind arbitrary values `bg-[#hex]`
- Roles: scope staff/master queries by `auth()->id()`; check `auth.user.role` in React
- `report` screen: only generate for admin + master — staff gets 403
- Docker `.env`: always `DB_HOST=db` (Docker service name, not localhost)
