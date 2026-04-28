# Deploy — HT ភ្នាក់ Lotto on VPS

Deploy the **HT Lotto App** to a Linux VPS using Docker Compose.

---

## VPS Requirements

| Item | Minimum | Recommended |
|---|---|---|
| OS | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |
| RAM | 1 GB | 2 GB |
| CPU | 1 vCPU | 2 vCPU |
| Disk | 20 GB SSD | 40 GB SSD |
| Open ports | 22, 80, 443 | 22, 80, 443 |

---

## 1. First-Time VPS Setup

Create `scripts/server-setup.sh` and run it on your VPS:

```bash
#!/bin/bash
set -e

# 1. System update
apt-get update && apt-get upgrade -y

# 2. Install Docker
apt-get install -y ca-certificates curl gnupg
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
  | tee /etc/apt/sources.list.d/docker.list > /dev/null

apt-get update
apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# 3. Add deploy user
useradd -m -s /bin/bash deploy
usermod -aG docker deploy
mkdir -p /home/deploy/.ssh
cp ~/.ssh/authorized_keys /home/deploy/.ssh/
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys

# 4. Create app directory
mkdir -p /var/www/lotto
chown -R deploy:deploy /var/www/lotto

# 5. Install Certbot for SSL
apt-get install -y certbot python3-certbot-nginx

echo "Server setup complete"
```

**Run on VPS:**
```bash
ssh root@YOUR_VPS_IP
bash server-setup.sh
```

---

## 2. Production Docker Compose (`docker-compose.prod.yml`)

Create this file at project root:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/app/Dockerfile
      target: production
    container_name: lotto_app
    restart: always
    working_dir: /var/www
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./src/storage:/var/www/storage
      - ./src/bootstrap/cache:/var/www/bootstrap/cache
    depends_on:
      db:
        condition: service_healthy
    networks:
      - lotto_net

  webserver:
    image: nginx:alpine
    container_name: lotto_nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./src:/var/www
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt:ro
      - /var/www/certbot:/var/www/certbot:ro
    depends_on:
      - app
    networks:
      - lotto_net

  db:
    image: mysql:8.0
    container_name: lotto_db
    restart: always
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    # No port mapping — DB must not be reachable from outside Docker
    volumes:
      - lotto_mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - lotto_net

networks:
  lotto_net:
    driver: bridge

volumes:
  lotto_mysql_data:
```

---

## 3. Production Nginx Config (`docker/nginx/prod.conf`)

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate     /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    root /var/www/public;
    index index.php index.html;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 4. Production Dockerfile (`docker/app/Dockerfile`)

Multi-stage build — Node builds assets, PHP serves them:

```dockerfile
# Stage 1: Node build
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY src/package*.json ./
RUN npm ci
COPY src .
RUN npm run build

# Stage 2: Production PHP
FROM php:8.3-fpm AS production

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY src .
COPY --from=node-builder /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

---

## 5. Production `.env` Template

Copy `src/.env.example` to `src/.env` on the VPS and fill in real values:

```env
APP_NAME="HT Lotto"
APP_ENV=production
APP_KEY=           # generate: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=lotto_db
DB_USERNAME=lotto_user
DB_PASSWORD=CHANGE_ME_STRONG_PASSWORD

SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=yourdomain.com
SESSION_SECURE_COOKIE=true

CACHE_STORE=file
QUEUE_CONNECTION=sync

DEBUGBAR_ENABLED=false
```

> Never commit `.env` to git. Copy it manually to the VPS.

---

## 6. Deploy Script (`scripts/deploy.sh`)

```bash
#!/bin/bash
set -e

APP_DIR="/var/www/lotto"
BRANCH="${1:-main}"

echo "Deploying branch: $BRANCH"

cd $APP_DIR

git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

[ ! -f src/.env ] && cp src/.env.example src/.env

docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d --no-deps app webserver

docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

docker compose -f docker-compose.prod.yml exec -T app \
    chown -R www-data:www-data storage bootstrap/cache

echo "Deploy complete"
```

**Run:**
```bash
ssh deploy@YOUR_VPS_IP
cd /var/www/lotto
bash scripts/deploy.sh main
```

---

## 7. SSL Setup with Let's Encrypt

```bash
# Run on VPS after HTTP is working
certbot certonly --webroot \
    -w /var/www/certbot \
    -d yourdomain.com \
    -d www.yourdomain.com \
    --email admin@yourdomain.com \
    --agree-tos \
    --non-interactive

# Auto-renew via cron
echo "0 12 * * * certbot renew --quiet && docker compose -f /var/www/lotto/docker-compose.prod.yml restart webserver" \
    | crontab -
```

---

## 8. GitHub Actions CI/CD (`.github/workflows/deploy.yml`)

Auto-deploy on push to `main`:

```yaml
name: Deploy to VPS

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.VPS_HOST }}
          username: deploy
          key: ${{ secrets.VPS_SSH_KEY }}
          script: |
            cd /var/www/lotto
            bash scripts/deploy.sh main
```

**GitHub Secrets to configure:**

| Secret | Value |
|---|---|
| `VPS_HOST` | Your VPS IP or domain |
| `VPS_SSH_KEY` | Private SSH key of the `deploy` user |

---

## 9. First Deploy — Step by Step

```bash
# 1. SSH into VPS
ssh deploy@YOUR_VPS_IP

# 2. Clone repo
git clone git@github.com:your-org/lotto2026.git /var/www/lotto
cd /var/www/lotto

# 3. Copy and edit env
cp src/.env.example src/.env
nano src/.env   # fill in APP_KEY, DB_PASSWORD, APP_URL

# 4. Generate APP_KEY
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show
# Paste the output into src/.env as APP_KEY=

# 5. Start DB first
docker compose -f docker-compose.prod.yml up -d db

# 6. Run migrations and seed admin user
docker compose -f docker-compose.prod.yml run --rm app php artisan migrate --seed

# 7. Start all services
docker compose -f docker-compose.prod.yml up -d

# 8. Setup SSL (after DNS is pointed to this VPS)
certbot certonly --webroot -w /var/www/certbot -d yourdomain.com

# 9. Restart webserver with HTTPS config
docker compose -f docker-compose.prod.yml restart webserver

# 10. Verify
curl -I https://yourdomain.com
docker compose -f docker-compose.prod.yml ps
```

> After first deploy, change the default admin password immediately.

---

## 10. Useful Commands

```bash
# Container status
docker compose -f docker-compose.prod.yml ps

# View logs
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f webserver
docker compose -f docker-compose.prod.yml logs -f db

# Enter app container
docker compose -f docker-compose.prod.yml exec app bash

# Run Artisan command
docker compose -f docker-compose.prod.yml exec app php artisan <command>

# MySQL shell
docker compose -f docker-compose.prod.yml exec db mysql -u lotto_user -p lotto_db

# Backup database
docker compose -f docker-compose.prod.yml exec db \
    mysqldump -u lotto_user -p lotto_db > backup_$(date +%Y%m%d).sql

# Restore database
cat backup_20260428.sql | docker compose -f docker-compose.prod.yml exec -T db \
    mysql -u lotto_user -p lotto_db
```

---

## 11. Rollback

```bash
cd /var/www/lotto

# Check recent commits
git log --oneline -10

# Roll back to previous commit
git checkout <previous-commit-hash>

# Rebuild and restart app
docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d --no-deps app
```

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] Strong `DB_PASSWORD` — not default
- [ ] `APP_KEY` is unique and set
- [ ] Port `3306` not exposed publicly (no host port mapping in `docker-compose.prod.yml`)
- [ ] SSH root login disabled (`PermitRootLogin no` in `/etc/ssh/sshd_config`)
- [ ] UFW firewall: only ports 22, 80, 443 open
- [ ] `.env` not in git (verify `.gitignore`)
- [ ] `phpmyadmin` service removed from production compose
- [ ] SSL certificate active and HTTPS works
- [ ] Auto-renew SSL cron job set
- [ ] Admin default password changed after first deploy
