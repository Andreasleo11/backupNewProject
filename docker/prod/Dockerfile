# =============================================================================
# Production Dockerfile for Laravel Application
# Best practice multi-stage build
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Base PHP with extensions
# -----------------------------------------------------------------------------
FROM php:8.2-fpm AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    # wkhtmltopdf dependencies (for Snappy / PDF generation)
    xfonts-75dpi \
    xfonts-base \
    libxrender1 \
    libfontconfig1 \
    libxext6 \
    # nginx
    nginx \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# Stage 2: Build frontend assets (Vite)
# -----------------------------------------------------------------------------
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 3: Install PHP dependencies
# -----------------------------------------------------------------------------
FROM base AS vendor

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

# -----------------------------------------------------------------------------
# Stage 4: Final Production Image
# -----------------------------------------------------------------------------
FROM base AS production

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy built frontend assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Copy vendor from vendor stage
COPY --from=vendor /var/www/html/vendor ./vendor

# Copy nginx configuration
COPY docker/prod/nginx/default.conf /etc/nginx/sites-available/default

# Create necessary directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy wkhtml binaries (from vendor) and make executable
RUN chmod +x vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64 2>/dev/null || true \
    && chmod +x vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64 2>/dev/null || true

# Environment variables for production
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Expose port
EXPOSE 80

# Start script (nginx + php-fpm)
COPY docker/prod/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
