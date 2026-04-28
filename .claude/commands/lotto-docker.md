# Lotto — Docker Setup

The project runs fully in Docker. No local PHP, MySQL, or Node installation needed.

## Services

| Service | Image | Port | Purpose |
|---|---|---|---|
| `app` | Custom PHP 8.3-FPM | — | Laravel application |
| `webserver` | `nginx:alpine` | `4040:80` | Nginx reverse proxy |
| `db` | `mysql:8.0` | `3306:3306` | MySQL database |
| `node` | `node:20-alpine` | `5173:5173` | Vite HMR (dev only) |
| `phpmyadmin` | `phpmyadmin:latest` | `4041:80` | DB GUI — dev only |

## File Structure
```
docker-compose.yml
docker-compose.prod.yml
docker/
  app/
    Dockerfile
    php.ini
  nginx/
    default.conf
```

## `docker-compose.yml`
```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/app/Dockerfile
    container_name: lotto_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
      - ./docker/app/php.ini:/usr/local/etc/php/conf.d/local.ini
    depends_on:
      - db
    networks:
      - lotto_net

  webserver:
    image: nginx:alpine
    container_name: lotto_nginx
    restart: unless-stopped
    ports:
      - "4040:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - lotto_net

  db:
    image: mysql:8.0
    container_name: lotto_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-lotto_db}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root_secret}   # ⚠ change in production
      MYSQL_USER: ${DB_USERNAME:-lotto_user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-lotto_pass}              # ⚠ change in production
    ports:
      - "3306:3306"   # dev only — remove host mapping in production
    volumes:
      - lotto_mysql_data:/var/lib/mysql
    networks:
      - lotto_net

  node:
    image: node:20-alpine
    container_name: lotto_node
    working_dir: /var/www
    volumes:
      - .:/var/www
    command: sh -c "npm install && npm run dev"
    ports:
      - "5173:5173"
    networks:
      - lotto_net

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: lotto_phpmyadmin
    restart: unless-stopped
    ports:
      - "4041:80"
    environment:
      PMA_HOST: db
      PMA_PORT: 3306
      PMA_USER: ${DB_USERNAME:-lotto_user}
      PMA_PASSWORD: ${DB_PASSWORD:-lotto_pass}
      UPLOAD_LIMIT: 64M
    depends_on:
      - db
    networks:
      - lotto_net

networks:
  lotto_net:
    driver: bridge

volumes:
  lotto_mysql_data:
```

## `docker/app/Dockerfile`
```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
```

## `docker/nginx/default.conf`
```nginx
server {
    listen 80;
    index index.php index.html;
    root /var/www/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## `.env` — Database + Sanctum Block
> **Never commit `.env` to git.** Add it to `.gitignore`.

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=lotto_db
DB_USERNAME=lotto_user
DB_PASSWORD=CHANGE_ME          # use a strong password — never commit this
DB_ROOT_PASSWORD=CHANGE_ME_ROOT

SANCTUM_STATEFUL_DOMAINS=localhost:4040
SESSION_DOMAIN=localhost

VITE_DEV_SERVER_HOST=0.0.0.0
```

## Common Commands
```bash
docker compose up -d                                        # Start all services
docker compose exec app php artisan migrate                 # Run migrations
docker compose exec app php artisan db:seed                 # Seed database
docker compose exec app php artisan <command>               # Any Artisan command
docker compose exec db mysql -u lotto_user -p lotto_db      # MySQL shell
docker compose logs -f app                                  # View app logs
docker compose down                                         # Stop all services
docker compose down -v                                      # Stop + remove volumes
```

## Rules
- Always use `db` (not `localhost`) as `DB_HOST` — it's the Docker service name
- MySQL internal port is `3306`; host mapping `3306:3306` is for external tools (TablePlus etc.)
- Vite HMR needs `VITE_DEV_SERVER_HOST=0.0.0.0` to be reachable from host browser
- Production: remove `node` and `phpmyadmin` services; run `npm run build` inside Dockerfile instead
- Migrations always run inside the `app` container, never on the host

---

## phpMyAdmin

**URL (dev):** `http://localhost:4041`

Auto-logs in using `DB_USERNAME` / `DB_PASSWORD` from `.env` via `PMA_USER` / `PMA_PASSWORD`.

### Access
| Field | Value |
|---|---|
| URL | `http://localhost:4041` |
| Server | `db` (auto-configured via `PMA_HOST`) |
| Username | value of `DB_USERNAME` in `.env` |
| Password | value of `DB_PASSWORD` in `.env` |

### Start only phpMyAdmin + db
```bash
docker compose up -d db phpmyadmin
```

### Stop phpMyAdmin without stopping other services
```bash
docker compose stop phpmyadmin
```

### Change upload limit
Edit `UPLOAD_LIMIT` in the service env (default `64M`).  
Useful when importing large SQL dump files.

### ⚠ Production warning
**Never include `phpmyadmin` in `docker-compose.prod.yml`.**  
Exposing phpMyAdmin on a public server is a critical security risk.  
If you need DB access on the VPS, use an SSH tunnel instead:
```bash
# Tunnel VPS MySQL to local port 3307 via SSH
ssh -L 3307:localhost:3306 deploy@YOUR_VPS_IP -N
# Then connect TablePlus/phpMyAdmin locally to 127.0.0.1:3307
```
