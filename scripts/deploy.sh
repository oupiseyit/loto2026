#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/lotto"
BRANCH="${1:-main}"

if [[ ! -d "$APP_DIR" ]]; then
  echo "Error: app directory not found: $APP_DIR" >&2
  exit 1
fi

cd "$APP_DIR"

if [[ ! -f "docker-compose.prod.yml" ]]; then
  echo "Error: docker-compose.prod.yml not found in $APP_DIR" >&2
  exit 1
fi

echo "Deploying branch: $BRANCH"

# Pull latest release code
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

# Create env from template only on first deploy
if [[ ! -f "src/.env" ]]; then
  cp src/.env.example src/.env
fi

# Build and restart app/web only
docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d --no-deps app webserver

# Apply non-destructive DB changes and cache optimizations
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# Ensure Laravel writable paths are correct
docker compose -f docker-compose.prod.yml exec -T app \
  chown -R www-data:www-data storage bootstrap/cache

echo "Deploy complete"
