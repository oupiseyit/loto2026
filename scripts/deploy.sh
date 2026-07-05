#!/usr/bin/env bash
set -euo pipefail

DEFAULT_APP_DIR="/var/www/lotto"
APP_DIR="${APP_DIR:-$DEFAULT_APP_DIR}"
BRANCH="${1:-main}"

if [[ ! -d "$APP_DIR" ]]; then
  if [[ -f "docker-compose.prod.yml" && -d "src" ]]; then
    APP_DIR="$(pwd)"
    echo "Warning: APP_DIR not found, using current directory: $APP_DIR" >&2
  else
    echo "Error: app directory not found: $APP_DIR" >&2
    echo "Hint: run from project root or set APP_DIR, e.g. APP_DIR=/var/www/lotto ./deploy.sh $BRANCH" >&2
    exit 1
  fi
fi

cd "$APP_DIR"

if [[ ! -f "docker-compose.prod.yml" ]]; then
  echo "Error: docker-compose.prod.yml not found in $APP_DIR" >&2
  exit 1
fi

echo "Deploying branch: $BRANCH"

# ── 1. Pull latest code ────────────────────────────────────────────────────────
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

# ── 2. Guard: ensure src/.env exists ──────────────────────────────────────────
if [[ ! -f "src/.env" ]]; then
  cp src/.env.example src/.env
  echo "WARNING: src/.env created from .env.example — fill in real values before re-running" >&2
  exit 1
fi

# ── 3. Remove Vite dev-server hot file if present ─────────────────────────────
rm -f src/public/hot

# ── 4. Build frontend assets (Vite) in a temporary Node container ─────────────
echo "Building frontend assets..."
docker run --rm \
  -v "$APP_DIR/src":/app \
  -w /app \
  node:20-alpine \
  sh -c "npm ci --prefer-offline && npm run build"

# ── 5. Build PHP image and restart app + webserver ────────────────────────────
docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d --no-deps app webserver

# ── 6. Install PHP dependencies (production only) ─────────────────────────────
docker compose -f docker-compose.prod.yml exec -T app \
  composer install --no-dev --optimize-autoloader --no-interaction

# ── 7. Apply non-destructive DB changes ───────────────────────────────────────
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

# ── 8. Rebuild Laravel caches ─────────────────────────────────────────────────
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# ── 9. Fix storage permissions ────────────────────────────────────────────────
docker compose -f docker-compose.prod.yml exec -T app \
  chown -R www-data:www-data storage bootstrap/cache

echo "Deploy complete"
