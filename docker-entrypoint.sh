#!/bin/bash
set -e

echo "========================================"
echo "Starting Laravel Application"
echo "========================================"

# Check if build directory exists
if [ ! -d "/var/www/html/public/build" ]; then
    echo "ERROR: Build directory not found!"
    exit 1
fi

# Check if manifest exists
if [ ! -f "/var/www/html/public/build/manifest.json" ]; then
    echo "ERROR: Vite manifest not found!"
    exit 1
fi

echo "✓ Vite assets found"

# Run migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration failed but continuing..."

# Clear and cache configs
echo "Caching configurations..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Application ready"
echo "========================================"

# Start Apache
exec apache2-foreground

