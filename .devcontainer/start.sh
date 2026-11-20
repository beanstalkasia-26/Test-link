#!/usr/bin/env bash
# Runs every time the Codespace starts: configures .env and serves the site on port 8000.
cd "$(dirname "$0")/../backend"

mkdir -p storage/app/public storage/app/private storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache database
touch database/database.sqlite
chmod -R 777 storage bootstrap/cache database

set_env() { grep -q "^$1=" .env && sed -i "s|^$1=.*|$1=$2|" .env || echo "$1=$2" >> .env; }
URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
set_env APP_URL "$URL"
set_env APP_DEBUG false
set_env DB_CONNECTION sqlite
set_env DB_DATABASE "$(pwd)/database/database.sqlite"
set_env SESSION_DRIVER file
set_env CACHE_STORE file
set_env QUEUE_CONNECTION sync
set_env TRUSTED_PROXIES '*'
set_env ADMIN_ENFORCE_2FA false
set_env SESSION_SECURE_COOKIE true
set_env INERTIA_SSR_ENABLED false

rm -f bootstrap/cache/*.php
php artisan config:clear >/dev/null 2>&1
php artisan storage:link --force >/dev/null 2>&1
php artisan migrate --force

PHP_CLI_SERVER_WORKERS=4 nohup php artisan serve --host=0.0.0.0 --port=8000 --no-reload > /tmp/serve.log 2>&1 &
echo "NAMTECH running at $URL"
