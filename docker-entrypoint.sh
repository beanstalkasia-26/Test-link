#!/bin/sh
set -e

echo "==> Preparing directories and permissions..."
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database

chmod -R 777 storage bootstrap/cache database 2>/dev/null || true

if [ -f database/database.sqlite ]; then
    chmod 666 database/database.sqlite 2>/dev/null || true
else
    touch database/database.sqlite
    chmod 666 database/database.sqlite 2>/dev/null || true
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Never ship a fixed key: use the APP_KEY variable if set, otherwise generate a private one.
if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=https://$RAILWAY_PUBLIC_DOMAIN|" .env
    export APP_URL="https://$RAILWAY_PUBLIC_DOMAIN"
fi

sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env 2>/dev/null || true
sed -i "s|^LOG_CHANNEL=.*|LOG_CHANNEL=stderr|" .env 2>/dev/null || true
sed -i "s|^TRUSTED_PROXIES=.*|TRUSTED_PROXIES=*|" .env 2>/dev/null || true

if ! grep -q '^INERTIA_SSR_ENABLED=' .env 2>/dev/null; then
    echo "INERTIA_SSR_ENABLED=false" >> .env
fi

if [ -z "$MYSQLHOST" ] && { [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ]; }; then
    sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=sqlite|" .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=/app/backend/database/database.sqlite|" .env
    sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env
    sed -i "s|^CACHE_STORE=.*|CACHE_STORE=file|" .env
    sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/app/backend/database/database.sqlite
fi

echo '==> Clearing config and framework caches...'
rm -f bootstrap/cache/*.php 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

php artisan storage:link --force 2>/dev/null || true

echo '==> Running database migrations...'
php artisan migrate --force 2>/dev/null || true

echo '==> Ensuring admin user...'
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>/dev/null || true
# Set SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD variables to choose the login; otherwise a password is generated and printed once in these logs.
php artisan db:seed --class=AdminUserSeeder --force || true

PORT="${PORT:-8080}"
echo "==> Starting NAMTech application on 0.0.0.0:$PORT with 4 workers..."
export PHP_CLI_SERVER_WORKERS=4
exec php artisan serve --host=0.0.0.0 --port="$PORT" --no-reload
