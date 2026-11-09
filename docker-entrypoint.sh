#!/bin/bash
set -e

# Support Render / Railway dynamic PORT (default to 80 if not set)
PORT="${PORT:-80}"
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Ensure storage directories exist
mkdir -p storage/app/public storage/app/document_templates storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create storage symlink if not already linked
php artisan storage:link --no-interaction || true

# Run database migrations if RUN_MIGRATIONS=true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "⚡ Running database migrations..."
    php artisan migrate --force --no-interaction || true
fi

# Cache configurations for production if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    echo "⚡ Caching Laravel configuration and routes..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Start real-time WebSocket server in background if present
if [ -f "websocket-server.js" ]; then
    echo "⚡ Starting background WebSocket server on port 8080..."
    node websocket-server.js &
fi

echo "⚡ Starting Apache server on port $PORT..."
exec apache2-foreground
