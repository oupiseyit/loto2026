# Lotto — Stack Architecture

Single Laravel project serving **two clients**:
- **Web** — React via Inertia.js (props passed directly, no API calls)
- **Mobile** — REST API via `routes/api.php` with Sanctum token auth

## Technology Map

| Layer | Technology | Location |
|---|---|---|
| Backend | Laravel 11 | `app/`, `routes/` |
| Web bridge | Inertia.js | `HandleInertiaRequests` middleware |
| Web UI | React 18 | `resources/js/Pages/` |
| Styling | Tailwind CSS | `resources/css/app.css` |
| Build | Vite | `vite.config.js` |
| Web auth | Laravel Breeze (Inertia+React) | `app/Http/Controllers/Auth/` |
| Mobile auth | Laravel Sanctum (Bearer token) | `routes/api.php` |
| Role guard | Custom `RoleMiddleware` | `app/Http/Middleware/RoleMiddleware.php` |
| Container | Docker + Docker Compose | `docker-compose.yml`, `docker/` |
| Database | MySQL 8.0 | Docker service `db` |

## Web — How Inertia Works
- Controller returns `Inertia::render('PageName', ['key' => $value])`
- React page receives data as props — **no Axios, no fetch, no useEffect for data**
- Forms: `useForm()` hook from `@inertiajs/react`, submit with `form.post(route('...'))`
- Navigation: `<Link href={route('home')}>` or `router.visit(route('home'))`
- Failed form: `redirect()->back()->withErrors($errors)`

## Mobile — How the API Works
- All endpoints: `/api/v1/` prefix in `routes/api.php`
- Auth: `Authorization: Bearer <sanctum-token>` header
- Login → returns token; stored by mobile app for all future requests
- Responses: `{ "success": bool, "data": {...}, "message": "..." }`
- Errors: `{ "success": false, "message": "...", "errors": {...} }`

## File Structure Per Screen

```
# Web (Inertia)
app/Http/Controllers/<Screen>Controller.php         ← Inertia::render
routes/web.php                                      ← web routes + role middleware

# Mobile API
app/Http/Controllers/Api/V1/<Screen>Controller.php  ← JSON response
app/Http/Resources/<Screen>Resource.php             ← JSON transformer
routes/api.php                                      ← /api/v1/ routes

# Shared
app/Http/Requests/<Screen>Request.php               ← validation (web + API)
database/migrations/                                ← DB schema

# React UI
resources/js/Pages/<Screen>.jsx                     ← Inertia page component
resources/js/Components/                            ← Shared components
resources/js/theme/colors.js                        ← Theme hex constants
```

## Key Rules
- Web controller: **never return JSON** — always `Inertia::render()`
- API controller: **never return Inertia** — always `response()->json()`
- `FormRequest` is shared between both web and API controllers
- **Staff** data is always scoped by `user_id = auth()->id()` — own bets only
- **Master** data scoped to own records + staff they created (`created_by = auth()->id()`)
- **Admin** data has no scope filter — sees everything

---

## Internationalisation (i18n)

**Default language:** English  
**Supported locales:**

| Code | Language |
|---|---|
| `en` | English (default) |
| `km` | Khmer |
| `vi` | Vietnamese |
| `th` | Thai |

### How it works

- Translation files: `lang/en.json`, `lang/km.json`, `lang/vi.json`, `lang/th.json`
- Library: `react-i18next` (web) / standard i18n JSON on mobile
- Locale stored in `localStorage` key `app_locale`; falls back to `en`
- Shared via `HandleInertiaRequests::share()` so every page receives `locale` as a prop:

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'locale' => session('locale', 'en'),
        'auth'   => ['user' => $request->user()],
    ]);
}
```

- Language switcher calls `router.post(route('locale.set'), { locale })` → stores in session
- React reads `const { locale } = usePage().props` and passes to `i18next.changeLanguage(locale)`

### Route for locale switch

```php
// routes/web.php
Route::post('/locale', fn(Request $request) => back()->with('locale', $request->locale))
    ->name('locale.set');
```

### React usage

```jsx
import { useTranslation } from 'react-i18next';
const { t } = useTranslation();
// <button>{t('submit')}</button>
```

---

## Dev Tools — Laravel Debugbar

**Package:** `barryvdh/laravel-debugbar` (dev only — never install in production)

### Install
```bash
docker compose exec app composer require barryvdh/laravel-debugbar --dev
docker compose exec app php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

### Enable only in local `.env`
```env
APP_ENV=local
APP_DEBUG=true
DEBUGBAR_ENABLED=true
```

In `config/debugbar.php`:
```php
'enabled' => env('DEBUGBAR_ENABLED', false),
```

> **Important:** `DEBUGBAR_ENABLED` defaults to `false` — it only activates when explicitly set to `true` in `.env`. Production `.env` must never have this set.

### Inertia.js compatibility
Debugbar injects an HTML toolbar — this works fine with Inertia **web** routes.  
It is **automatically disabled** for API routes (JSON responses) — no config needed.

To confirm it is excluded from API responses, check `config/debugbar.php`:
```php
'except' => [
    'telescope*',
    'horizon*',
    'api*',      // ← already excluded by default
],
```

### Docker note
Debugbar stores its data in `storage/debugbar/`. This directory is already volume-mounted in Docker — no extra config needed.

If the toolbar does not appear, ensure:
```bash
docker compose exec app php artisan storage:link
docker compose exec app chmod -R 775 storage/debugbar
```

### Usage in controllers (optional)
```php
// Add custom messages to the Debugbar timeline
\Debugbar::startMeasure('render', 'Rendering bets');
$bets = Bet::where('user_id', auth()->id())->get();
\Debugbar::stopMeasure('render');

// Add info/log message
\Debugbar::info('Bets loaded: ' . $bets->count());
```

### Disable per-request (useful for testing)
```php
\Debugbar::disable();
```

### Remove from production Dockerfile
The `docker/app/Dockerfile` uses `composer install --no-dev` for production — this automatically excludes Debugbar since it is a `--dev` dependency. No extra step needed.
