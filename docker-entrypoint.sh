#!/bin/bash
set -e

# Support Render / Railway dynamic PORT (default to 80 if not set)
PORT="${PORT:-80}"
echo "==> Configuring Apache for port $PORT..."
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Ensure storage, cache, and database directories exist
mkdir -p storage/app/public \
         storage/app/document_templates \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache \
         database

# Ensure an .env file exists if not provided
if [ ! -f .env ]; then
    echo "==> Initializing .env from .env.example..."
    cp .env.example .env
fi

# Ensure an APP_KEY is available
if [ -n "$APP_KEY" ]; then
    echo "==> Using APP_KEY from environment..."
    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    else
        echo "APP_KEY=$APP_KEY" >> .env
    fi
else
    if ! grep -q "^APP_KEY=base64:" .env; then
        echo "==> Generating application encryption key (APP_KEY)..."
        php artisan key:generate --force
    fi
fi

# Determine database connection (defaults to sqlite for frictionless zero-config deploy)
CURRENT_DB_CONN="${DB_CONNECTION:-sqlite}"

if [ "$CURRENT_DB_CONN" = "sqlite" ]; then
    SQLITE_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    echo "==> Using SQLite database at $SQLITE_PATH"
    if [ ! -f "$SQLITE_PATH" ]; then
        echo "==> Creating SQLite database file..."
        touch "$SQLITE_PATH"
    fi
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
    chmod 664 "$SQLITE_PATH"
else
    echo "==> Using database connection: $CURRENT_DB_CONN ($DB_HOST:$DB_PORT / $DB_DATABASE)"
fi

# Ensure web server ownership and write permissions
chown -R www-data:www-data storage bootstrap/cache /var/www/html/database
chmod -R 775 storage bootstrap/cache /var/www/html/database

# Create storage symlink
php artisan storage:link --no-interaction || true

# Always run database migrations so tables (including sessions & document_types) exist
echo "==> Running database migrations..."
php artisan migrate --force --no-interaction || true

# Seed default database records (admin, resident, and document types)
echo "==> Running database seeders..."
php artisan db:seed --force --no-interaction || true

# Cache Laravel configuration, routes, and views
echo "==> Optimizing Laravel configuration and caches..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start background WebSocket server if present and ws module is installed
if [ -f "websocket-server.js" ] && [ -d "node_modules/ws" ]; then
    echo "==> Starting background WebSocket server on port 8080..."
    node websocket-server.js &
fi

echo "==> Starting Apache server on port $PORT..."
exec apache2-foreground