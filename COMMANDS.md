# COMMANDS — HT ភ្នាក់ Lotto

---

## Docker

```bash
# Start / stop
docker compose up -d                        # Start all services (detached)
docker compose down                         # Stop all services
docker compose restart                      # Restart all services
docker compose logs -f app                  # Stream app logs
docker compose logs -f nginx               # Stream nginx logs

# Shell access
docker compose exec app bash               # Shell into PHP-FPM container
docker compose exec db mysql -u root -p    # MySQL CLI
```



---

## Artisan (run inside Docker)

### Database
```bash
docker compose exec app php artisan migrate              # Run pending migrations
docker compose exec app php artisan migrate:fresh        # Drop all tables & re-migrate
docker compose exec app php artisan migrate:fresh --seed # Fresh migrate + seed
docker compose exec app php artisan db:seed              # Run all seeders
docker compose exec app php artisan db:seed --class=CurrencySeeder
docker compose exec app php artisan db:seed --class=DatabaseSeeder
docker compose exec app php artisan db:wipe              # Drop all tables
```

### Cache & Optimize
```bash
docker compose exec app php artisan cache:clear          # Clear app cache
docker compose exec app php artisan config:clear         # Clear config cache
docker compose exec app php artisan route:clear          # Clear route cache
docker compose exec app php artisan view:clear           # Clear compiled views
docker compose exec app php artisan optimize             # Cache config + routes + views
docker compose exec app php artisan optimize:clear       # Clear all caches
```

### Livewire
```bash
docker compose exec app php artisan livewire:make <Name>       # Create new component
docker compose exec app php artisan livewire:publish --assets  # Publish Livewire JS assets
docker compose exec app php artisan livewire:delete <Name>     # Delete component
```

### Make (scaffolding)
```bash
docker compose exec app php artisan make:controller <Name>     # Controller
docker compose exec app php artisan make:model <Name> -m       # Model + migration
docker compose exec app php artisan make:migration <name>      # Migration only
docker compose exec app php artisan make:seeder <Name>         # Seeder
docker compose exec app php artisan make:request <Name>        # Form request
docker compose exec app php artisan make:resource <Name>       # API resource
docker compose exec app php artisan make:middleware <Name>     # Middleware
```

### Testing
```bash
docker compose exec app php artisan test                       # Run all tests
docker compose exec app php artisan test --filter=<TestName>   # Run specific test
docker compose exec app php artisan test --coverage            # With coverage report
```

### Swagger / API Docs
```bash
docker compose exec app php artisan l5-swagger:generate        # Regenerate Swagger docs
```

### Key & App
```bash
docker compose exec app php artisan key:generate               # Generate app key
docker compose exec app php artisan tinker                     # Laravel REPL
docker compose exec app php artisan about                      # App info
docker compose exec app php artisan env                        # Show current environment
docker compose exec app php artisan down                       # Maintenance mode on
docker compose exec app php artisan up                         # Maintenance mode off
```

---

## Node / Vite (run inside `src/`)

```bash
# From project root
docker compose exec node npm run dev       # Start Vite HMR dev server
docker compose exec node npm run build     # Build assets for production
```

---

## Screenshots (run from project root)

```bash
node screenShort/capture.js               # Capture all (web + mobile, all 3 roles)
node screenShort/capture-web.js           # Web only  (1440×900)
node screenShort/capture-mobile.js        # Mobile only (393×852)
```

Output saved to:
- `screenShort/web/{admin,master,staff}/`
- `screenShort/mobile/{admin,master,staff}/`

**Requires:** app running at `http://localhost` + test accounts in DB.

---

## URLs (local dev)

| Service     | URL                          |
|-------------|------------------------------|
| App         | http://localhost        |
| phpMyAdmin  | http://localhost:4041        |
| Swagger UI  | http://localhost/api/documentation |
| Vite HMR    | http://localhost:5173        |

---

## Test Accounts

| Role   | Username | Password   |
|--------|----------|------------|
| Admin  | admin    | admin123   |
| Master | master1  | master123  |
| Staff  | staff1   | staff123   |
