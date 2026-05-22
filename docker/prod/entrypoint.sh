#!/bin/bash
set -e

echo "Starting Laravel production container..."

# Run Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
exec nginx -g "daemon off;"
