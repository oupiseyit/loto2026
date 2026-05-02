# Laravel Boost — Project Context

## Package Versions

- php - 8.3
- laravel/framework - v13
- laravel/sanctum - v4
- livewire/livewire - v3
- laravel/boost - v2
- alpinejs - v3
- tailwindcss - v3
- phpunit/phpunit - v11

## Boost MCP Tools

- Use `database-query` to run read-only queries against the database.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct URL including Docker port before sharing with the user.
- Use `browser-logs` to read browser errors and exceptions. Only recent entries are useful.

## Searching Documentation

- Use `search-docs` for version-specific Laravel/Livewire docs before making changes.
- Pass a `packages` array to scope results. Use broad topic queries — `['rate limiting', 'routing']`.
- Do not include package names in queries; package info is shared automatically.

## Artisan

- Run inside the container: `docker compose exec app php artisan <cmd>`
- Inspect routes: `php artisan route:list` with filters `--method`, `--name`, `--path`, `--except-vendor`
- Check config: `php artisan config:show app.name`

## Tinker

- Always use single quotes: `php artisan tinker --execute 'User::count();'`
- Use double quotes inside for PHP strings: `'User::where("active", true)->count();'`

## PHP Conventions

- Always use curly braces for control structures, even single-line.
- Use PHP 8 constructor property promotion.
- Use explicit return types and type hints on all methods.
- Use TitleCase for Enum keys.

## Livewire

- Keep state server-side. Validate and authorize in actions as in HTTP requests.
- Use Alpine.js for client-side interactions — no separate JS framework.

## Laravel Pint

- After modifying PHP files run: `docker compose exec app vendor/bin/pint --dirty`
