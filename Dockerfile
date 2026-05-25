# Production Dockerfile for Laravel (used only for prod builds)
#
# IMPORTANT: Keep this in sync with dev environment (compose.yaml)
# Current dev uses: PHP 8.3 (via vendor/laravel/sail/runtimes/8.3)
# This file must target the SAME PHP major version to avoid "works on my machine" issues.

# ============================================
# Stage 1: Frontend assets (Vite + Tailwind)
# ============================================
# Node 20 matches the version used in Sail 8.3 runtime (as of May 2026)
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build


# ============================================
# Stage 2: PHP + Composer dependencies
# ============================================
FROM php:8.3-fpm-alpine AS app

# Install system packages + PHP extensions required by this project
RUN apk add --no-cache \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        icu-libs \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Create required Laravel cache directories early (needed for composer post-install scripts)
RUN mkdir -p \
    bootstrap/cache \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# Copy only composer files first (better caching)
COPY composer.json composer.lock artisan ./

# Copy the minimum files needed for composer to run successfully
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY routes/ routes/
COPY resources/ resources/

# Install PHP dependencies (production only)
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-progress

# Copy application code
COPY . .

# Copy built frontend assets from previous stage
COPY --from=frontend /app/public/build ./public/build

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose php-fpm port
EXPOSE 9000

CMD ["php-fpm"]
