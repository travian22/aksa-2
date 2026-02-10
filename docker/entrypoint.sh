#!/bin/sh
set -e

echo "==> Starting AKSA Backend..."

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "==> Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "==> Running migrations..."
php artisan migrate --force

# Cache configuration for performance
echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure storage link exists
php artisan storage:link 2>/dev/null || true

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> AKSA Backend is ready!"

exec "$@"

