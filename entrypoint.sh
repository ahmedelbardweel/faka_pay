#!/bin/bash

# Exit on error
set -e

echo "--- 🚀 Launching Production Environment ---"

# Ensure storage directories exist and have correct permissions
mkdir -p storage/framework/{sessions,views,cache}
chown -R www-data:www-data storage bootstrap/cache

echo "--- 🛠️ Running Migrations ---"
# Run migrations with a slight delay to ensure DB is ready if needed
php artisan migrate --force || echo "⚠️ Migration failed, but proceeding anyway..."

echo "--- 🧹 Clearing Caches ---"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- 🌐 Starting Apache ---"
apache2-foreground
