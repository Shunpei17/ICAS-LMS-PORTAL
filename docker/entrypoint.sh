#!/bin/bash
set -e

echo "🚀 Starting ICAS LMS Portal..."

# ---- Resolve the PORT for Nginx ----
export PORT="${PORT:-8080}"
echo "📡 Configuring Nginx on port $PORT..."

# Replace the RAILWAY_PORT placeholder in nginx config with actual port
sed -i "s/RAILWAY_PORT/$PORT/g" /etc/nginx/sites-available/default

# ---- Ensure writable directories ----
echo "📁 Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p /var/log/supervisor

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---- Create storage symlink ----
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ---- Clear old caches first ----
echo "🧹 Clearing old caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# ---- Run database migrations FIRST (before caching) ----
echo "🗄️  Running database migrations..."
php artisan migrate --force --no-interaction

# ---- Seed demo users ----
echo "🌱 Seeding demo users..."
php artisan db:seed --force --no-interaction

# ---- Cache configuration AFTER migrations ----
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Initialization complete! Starting services..."

# Execute the main process (supervisord)
exec "$@"
