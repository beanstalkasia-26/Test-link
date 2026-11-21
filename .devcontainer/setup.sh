#!/usr/bin/env bash
# One-time setup when the Codespace is created.
set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "==> Installing PHP extensions"
sudo apt-get update -y
sudo apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libzip-dev
sudo docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
sudo docker-php-ext-install -j"$(nproc)" gd zip bcmath pdo_sqlite

echo "==> Building frontend"
cd "$ROOT/frontend"
npm install
mkdir -p "$ROOT/backend/public/build" "$ROOT/backend/bootstrap/ssr"
npm run build

echo "==> Installing backend"
cd "$ROOT/backend"
composer install --no-interaction --optimize-autoloader
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64:' .env || php artisan key:generate --force
