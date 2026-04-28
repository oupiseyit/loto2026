# CLAUDE.md — HT ភ្នាក់ Lotto Project

This file is loaded automatically at the start of every Claude Code session. It provides project-wide conventions so Claude does not need to rediscover them.

---

## Project Identity

**App:** HT ភ្នាក់ (HT Lottery Agent) — Cambodian lottery agent management system  
**Stack:** Laravel 11 + Inertia.js + React 18 + Tailwind CSS + Vite  
**Mobile:** Laravel Sanctum Bearer token API (`routes/api.php`)  
**Roles:** `admin` | `master` | `staff`  
**Docker:** PHP 8.3-FPM + Nginx + MySQL 8.0 + Node 20 + phpMyAdmin

---

## Skill Commands

All code generation skills live in `.claude/commands/`. Use them instead of writing from scratch:

| Command | Purpose |
|---|---|
| `/lotto-screen <screen> [theme]` | Generate full screen (web + API + React) |
| `/lotto-design` | Color themes and design tokens |
| `/lotto-screens` | Layout spec for all 7 screens |
| `/lotto-roles` | Role permissions and middleware |
| `/lotto-stack` | Architecture and file structure |
| `/lotto-docker` | Docker Compose and Dockerfile |
| `/lotto-api` | Mobile API endpoint reference |
| `/lotto-swagger` | Swagger/OpenAPI annotation guide |
| `/lotto-test` | PHPUnit test generation |
| `/lotto-db` | Database schema and seeder |
| `/deploy` | VPS deployment and CI/CD |

---

## Hard Rules — Never Break These

### Web vs API controllers
- Web controller (`app/Http/Controllers/`) → **always** `Inertia::render()` — never JSON
- API controller (`app/Http/Controllers/Api/V1/`) → **always** `response()->json()` — never Inertia
- `FormRequest` classes are shared between both

### React / Inertia
- Forms: use `useForm()` from `@inertiajs/react` — no Axios, no fetch, no useEffect for data loading
- Navigation: `<Link href={route('name')}>` or `router.visit()`
- Data comes from Inertia props — never call API from React web pages
- Theme colors: import hex from `resources/js/theme/colors.js`, use Tailwind arbitrary values `bg-[#hex]`

### Docker
- `DB_HOST=db` — always the Docker service name, never `localhost`
- Run all Artisan commands inside the container: `docker compose exec app php artisan ...`
- Migrations run inside the `app` container, never on the host

### Role scoping (Eloquent)
- **staff** → `->where('user_id', auth()->id())`
- **master** → `->where('created_by', auth()->id())` for their staff's data
- **admin** → no scope filter
- Always check `auth()->user()->role` before query — never trust client input for role

### Security
- Never expose `phpmyadmin` service in production
- Never commit `.env` — use `.env.example` for template
- Passwords in `.env` use `CHANGE_ME` as placeholder — never real passwords in code

---

## Docker Commands — Quick Reference

```bash
docker compose up -d                              # Start all services
docker compose exec app php artisan migrate       # Run migrations
docker compose exec app php artisan db:seed       # Seed database
docker compose exec app php artisan <cmd>         # Any Artisan command
docker compose logs -f app                        # App logs
docker compose down                               # Stop services
```

phpMyAdmin: `http://localhost:4041`  
App: `http://localhost`  
Vite HMR: `http://localhost:5173`

---

## Project Structure

```
project-root/
├── docker/
│   ├── nginx/default.conf      ← Nginx config
│   └── php/
│       ├── Dockerfile          ← PHP 8.3-FPM image
│       └── local.ini           ← PHP settings
├── src/                        ← Laravel application
│   ├── app/Http/Controllers/           ← Web (Inertia) controllers
│   ├── app/Http/Controllers/Api/V1/    ← Mobile API controllers
│   ├── app/Http/Requests/              ← Shared FormRequests
│   ├── app/Http/Resources/             ← API JSON transformers
│   ├── app/Http/Middleware/            ← RoleMiddleware
│   ├── app/Virtual/                    ← Swagger virtual schema classes
│   ├── resources/js/Pages/             ← React page components
│   ├── resources/js/Components/        ← Shared React components
│   ├── resources/js/theme/colors.js    ← Theme hex constants
│   ├── routes/web.php                  ← Inertia routes
│   ├── routes/api.php                  ← /api/v1/ routes
│   ├── tests/Feature/Web/              ← Inertia web tests
│   ├── tests/Feature/Api/              ← API tests
│   ├── database/migrations/            ← DB schema
│   ├── database/seeders/               ← Seeders (AdminSeeder first)
│   └── .env                            ← Laravel environment
├── .env                        ← Docker Compose variables only
├── .dockerignore
└── docker-compose.yml
```

---

## Testing Conventions

- All tests extend `Tests\Feature\BaseTestCase`
- Use `actingAsAdmin()`, `actingAsMaster()`, `actingAsStaff()` helpers
- Use `apiAs($user)` for API token auth in tests
- Test coverage per endpoint: happy path + 422 validation + 403 wrong role + 401 unauthenticated
- Run: `docker compose exec app php artisan test`

---

## Database Conventions

- All money: `DECIMAL(12,2)` — never FLOAT
- `created_by` on `users` table: NULL for admin, master's `id` for staff
- `user_id` denormalized on `bets` table for fast scoping without joins
- Migration order: users → tickets → bets → results → settings
- First deploy: run `AdminSeeder` then change admin password immediately

---

## Dev Tools (local only)

- **Laravel Debugbar**: enabled via `DEBUGBAR_ENABLED=true` in `.env` (dev only)
- **phpMyAdmin**: `http://localhost:4041` (never in production)
- **Swagger UI**: `http://localhost/api/documentation`

---

## Language

**Default:** English  
**Supported:** English | ខ្មែរ (Khmer) | Tiếng Việt (Vietnamese) | ภาษาไทย (Thai)

Store the user's language preference in `localStorage` key `app_locale`. Pass it as an Inertia shared prop via `HandleInertiaRequests` so all pages can read it without a page reload.

| Locale code | Language |
|---|---|
| `en` | English (default) |
| `km` | Khmer |
| `vi` | Vietnamese |
| `th` | Thai |

Translation files live in `lang/{locale}.json` (JSON format, loaded by `react-i18next` or equivalent).

### Key Khmer UI terms (used in Inertia props + table headers)

| Khmer | Meaning |
|---|---|
| `ព្រឹក` | Morning session |
| `ថ្ងៃ` | Noon session |
| `ល្ងាច` | Evening session |
| `បញ្ជូន` | Submit |
| `សរុប` | Total |
