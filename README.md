# HT ភ្នាក់ — Lottery Agent System

Cambodian lottery agent management system built with Laravel 11 + Inertia.js + React 18.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 |
| Frontend | React 18 + Inertia.js |
| Styling | Tailwind CSS + Vite |
| Mobile API | Laravel Sanctum (Bearer token) |
| Database | MySQL 8.0 |
| Container | Docker + Docker Compose |

## Roles

| Role | Access |
|---|---|
| `admin` | Full access — all screens + reports + enter results |
| `master` | Create staff + view reports + betting |
| `staff` | Home/betting only |

---

## Project Structure

```
Lotto2026/
├── docker/
│   ├── nginx/
│   │   └── default.conf        # Nginx reverse proxy config
│   └── php/
│       ├── Dockerfile          # PHP 8.3-FPM image
│       └── local.ini           # PHP settings
├── src/                        # Laravel application
│   ├── app/
│   ├── database/
│   ├── resources/js/           # React pages + components
│   ├── routes/
│   ├── lang/                   # en / km / vi / th translations
│   └── .env                    # Laravel environment (do not commit)
├── .env                        # Docker Compose variables only
├── .dockerignore
├── docker-compose.yml
└── README.md
```

---

## Getting Started

### Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running
- No local PHP, MySQL, or Node needed

### 1. Clone the repository

```bash
git clone <repo-url> Lotto2026
cd Lotto2026
```

### 2. Set up environment files

```bash
# Docker Compose variables (already has defaults)
cp .env.example .env 2>/dev/null || true

# Laravel application environment
cp src/.env.example src/.env
```

Edit `src/.env` and set a strong `APP_KEY` if not already set:

```bash
# Generate app key after containers are up (step 4)
docker compose exec app php artisan key:generate
```

### 3. Build the Docker image

```bash
docker compose build app
```

### 4. Start all services

```bash
docker compose up -d
```

This starts:
| Container | Purpose | Port |
|---|---|---|
| `lotto_app` | PHP 8.3-FPM (Laravel) | — |
| `lotto_nginx` | Nginx web server | `4040` |
| `lotto_db` | MySQL 8.0 | `3306` |
| `lotto_phpmyadmin` | phpMyAdmin GUI | `4041` |

### 5. Install PHP dependencies

```bash
docker compose exec app composer install
```

### 6. Run database migrations

```bash
docker compose exec app php artisan migrate
```

```bash
docker compose exec app php artisan migrate:refresh --seed
```

### 7. Seed the database

```bash
# Create admin + sample master & staff users
docker compose exec app php artisan db:seed
```

Default accounts after seeding:

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | Admin |
| `master1` | `master123` | Master |
| `master2` | `master123` | Master |
| `staff1` | `staff123` | Staff (under master1) |
| `staff2` | `staff123` | Staff (under master1) |
| `staff3` | `staff123` | Staff (under master2) |

> **Change all passwords immediately after first login.**

### 8. Build frontend assets

```bash
cd src && npm install && npm run build && cd ..
```

### 9. Open the app

| URL | Purpose |
|---|---|
| `http://localhost:4040` | Web application |
| `http://localhost:4040/login` | Login page |
| `http://localhost:4040/api/documentation` | Swagger API docs |
| `http://localhost:4041` | phpMyAdmin |

---

## Common Commands

All Artisan commands run inside the `app` container:

```bash
# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed --class=UserSeeder
docker compose exec app php artisan route:cache
docker compose exec app php artisan config:cache
docker compose exec app php artisan cache:clear

# Frontend (run from src/)
cd src && npm run build       # production build
cd src && npm run dev         # local dev with HMR (needs node container)

# Logs
docker compose logs -f app

# Stop services
docker compose down

# Stop + delete database volume
docker compose down -v
```

---

## Mobile API

Base URL: `http://localhost:4040/api/v1`

Authentication: `Authorization: Bearer <token>`

```bash
# Login
curl -X POST http://localhost:4040/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Use returned token for subsequent requests
curl http://localhost:4040/api/v1/me \
  -H "Authorization: Bearer <token>"
```

---

## Running Tests

All tests run inside the `app` container using PHPUnit.

### Run all tests

```bash
docker compose exec app php artisan test
```

### Run a specific test file

```bash
docker compose exec app php artisan test tests/Feature/Web/HomeTest.php
docker compose exec app php artisan test tests/Feature/Api/AuthTest.php
```

### Run tests by filter (method name)

```bash
docker compose exec app php artisan test --filter=test_admin_can_login
docker compose exec app php artisan test --filter=HomeTest
```

### Run tests by group

```bash
docker compose exec app php artisan test --group=web
docker compose exec app php artisan test --group=api
```

### Run with coverage report (requires Xdebug)

```bash
docker compose exec app php artisan test --coverage
docker compose exec app php artisan test --coverage --min=80
```

### Test structure

```
src/tests/
├── Feature/
│   ├── Web/          # Inertia web route tests (browser session)
│   │   ├── AuthTest.php
│   │   ├── HomeTest.php
│   │   ├── RecordTest.php
│   │   ├── ResultTest.php
│   │   ├── ReportTest.php
│   │   ├── SettingTest.php
│   │   └── AccountTest.php
│   └── Api/          # REST API tests (Sanctum token)
│       ├── AuthTest.php
│       ├── BetTest.php
│       ├── RecordTest.php
│       ├── ResultTest.php
│       ├── ReportTest.php
│       ├── SettingTest.php
│       └── AccountTest.php
└── TestCase.php
```

### Test helpers (defined in `BaseTestCase`)

```php
$this->actingAsAdmin();    // login as admin role
$this->actingAsMaster();   // login as master role
$this->actingAsStaff();    // login as staff role
$this->apiAs($user);       // attach Sanctum token for API tests
```

### Each endpoint is tested for

| Scenario | Expected |
|---|---|
| Happy path (correct role) | `200` / `201` |
| Validation error | `422` |
| Wrong role | `403` |
| Unauthenticated | `401` / `302` redirect |

### Reset test database before running

```bash
docker compose exec app php artisan migrate:fresh --seed --env=testing
docker compose exec app php artisan test
```

> Tests use `RefreshDatabase` — the database is reset between each test automatically.

---

## Supported Languages

The UI supports 4 languages (switcher in settings):

| Code | Language |
|---|---|
| `en` | English (default) |
| `km` | ខ្មែរ (Khmer) |
| `vi` | Tiếng Việt (Vietnamese) |
| `th` | ภาษาไทย (Thai) |

Translation files: `src/lang/{en,km,vi,th}.json`

---

## Screenshot Capture

Automated screenshot capture using Puppeteer. Captures all pages for every role (admin, master, staff) in both desktop and mobile viewports.

### Prerequisites

```bash
# Install Puppeteer (from project root, not src/)
npm install
```

> Puppeteer is installed at the project root `package.json`, not inside `src/`.

### Usage

```bash
# Capture both web + mobile screenshots
node screenShort/capture.js

# Capture desktop only (1440 × 900)
node screenShort/capture-web.js

# Capture mobile only (393 × 852 @3x — iPhone 14 Pro)
node screenShort/capture-mobile.js
```

### Requirements

- Docker containers must be running (`docker compose up -d`)
- App accessible at `http://localhost:4040`
- Database seeded with default accounts (`docker compose exec app php artisan db:seed`)

### Output Structure

```
screenShort/
├── capture.js              # Master runner (executes web + mobile)
├── capture-web.js          # Desktop capture script
├── capture-mobile.js       # Mobile capture script
├── web/
│   ├── admin/              # 7 screenshots (login, home, record, result, setting, account, report)
│   ├── master/             # 7 screenshots
│   └── staff/              # 5 screenshots (no setting/report)
└── mobile/
    ├── admin/              # 7 screenshots
    ├── master/             # 7 screenshots
    └── staff/              # 5 screenshots
```

### Viewports

| Script | Width | Height | Device |
|---|---|---|---|
| `capture-web.js` | 1440 | 900 | Desktop |
| `capture-mobile.js` | 393 | 852 | iPhone 14 Pro (@3x) |

---

## Development Notes

- **Web routes** always use `Inertia::render()` — never return JSON
- **API routes** always use `response()->json()` — never Inertia
- **DB_HOST** must be `db` (Docker service name) — never `localhost`
- **phpMyAdmin** must never be exposed in production
- All money columns use `DECIMAL(12,2)` — never FLOAT

---

## Production Deployment

See [`.claude/commands/deploy.md`](.claude/commands/deploy.md) for full VPS deployment guide including:
- Docker Compose production config
- Nginx SSL + Let's Encrypt
- GitHub Actions CI/CD
- Security checklist
