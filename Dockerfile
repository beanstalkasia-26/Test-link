# Stage 1: Build Frontend Assets
FROM node:22-alpine AS frontend
WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./frontend/
RUN cd frontend && npm install

COPY frontend/ ./frontend/
COPY backend/ ./backend/
RUN mkdir -p /app/backend/public/build /app/backend/bootstrap/ssr && cd frontend && npm run build

# Stage 2: PHP Application Runtime
FROM php:8.3-cli-alpine
WORKDIR /app/backend

RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    sqlite-dev \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_sqlite gd zip bcmath pcntl

RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/docker-php-memory-limit.ini \
    && echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini

ENV APP_KEY=base64:V0tED2uh1TWtaNsv9FWCo86+7JrmSHjLrAwNJb/3hzw=

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY backend/ ./
COPY --from=frontend /app/backend/public/build ./public/build
COPY --from=frontend /app/backend/bootstrap/ssr ./bootstrap/ssr

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
    && chmod -R 777 storage bootstrap/cache database

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i -e 's/\r$//' /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/bin/sh", "/usr/local/bin/docker-entrypoint.sh"]
