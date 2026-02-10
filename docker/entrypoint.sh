#!/bin/sh
set -e

echo "==> Starting AKSA Backend..."

# Wait for database to be ready
echo "==> Waiting for database..."
MAX_RETRIES=30
RETRY_COUNT=0
until mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" > /dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo "==> ERROR: Database not available after $MAX_RETRIES attempts. Starting anyway..."
        break
    fi
    echo "==> Database not ready yet... ($RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done
echo "==> Database is ready!"

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
