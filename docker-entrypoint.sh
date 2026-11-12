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
    if [ -f .env.example ]; then
        echo "==> Initializing .env from .env.example..."
        cp .env.example .env
    else
        echo "==> .env.example not found, generating default .env..."
        cat << 'EOF' > .env
APP_NAME="E-Barangay"
APP_ENV=production
APP_KEY=base64:VT6H850mEh4eNu9yS3lx7yJYiSqpcxXqBVg6p4+UYto=
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database

RECAPTCHA_SITE_KEY=6LemLRwuAAAAAPYzrDdOn-na4zxddesHAzhUl_Fe
RECAPTCHA_SECRET_KEY=6LemLRwuAAAAAJh0oU5U_cUzPXw0Mo1lUxJGuiVc

MAIL_MAILER=resend_http
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=cogoneast.system@gmail.com
MAIL_PASSWORD=jydkmltxbnpnbfkx
MAIL_ENCRYPTION=tls
MAIL_TIMEOUT=10
MAIL_FROM_ADDRESS=cogoneast.system@gmail.com
MAIL_FROM_NAME="E-Barangay Cogon East"
RESEND_API_KEY=
RESEND_FROM_ADDRESS=onboarding@resend.dev
EOF
    fi
fi

# Sanitize session settings in .env to prevent CSRF / 419 issues
sed -i "s|^SESSION_DOMAIN=.*|# SESSION_DOMAIN=|" .env
sed -i "s|^SESSION_DRIVER=database|SESSION_DRIVER=file|" .env

# Update APP_URL if RENDER_EXTERNAL_URL or APP_URL is provided
if [ -n "$RENDER_EXTERNAL_URL" ]; then
    echo "==> Setting APP_URL from RENDER_EXTERNAL_URL: $RENDER_EXTERNAL_URL"
    sed -i "s|^APP_URL=.*|APP_URL=$RENDER_EXTERNAL_URL|" .env
elif [ -n "$APP_URL" ]; then
    echo "==> Setting APP_URL: $APP_URL"
    sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
fi

# Ensure an APP_KEY is available (persistent across container boots)
DEFAULT_PERSISTENT_KEY="base64:VT6H850mEh4eNu9yS3lx7yJYiSqpcxXqBVg6p4+UYto="
if [ -n "$APP_KEY" ]; then
    echo "==> Using APP_KEY from environment..."
    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    else
        echo "APP_KEY=$APP_KEY" >> .env
    fi
else
    if ! grep -q "^APP_KEY=base64:" .env; then
        echo "==> Using default application encryption key..."
        if grep -q "^APP_KEY=" .env; then
            sed -i "s|^APP_KEY=.*|APP_KEY=$DEFAULT_PERSISTENT_KEY|" .env
        else
            echo "APP_KEY=$DEFAULT_PERSISTENT_KEY" >> .env
        fi
    fi
fi

# Ensure reCAPTCHA keys are configured in .env (supports Render environment variable overrides)
CURRENT_RECAPTCHA_SITE="${RECAPTCHA_SITE_KEY:-6LemLRwuAAAAAPYzrDdOn-na4zxddesHAzhUl_Fe}"
CURRENT_RECAPTCHA_SECRET="${RECAPTCHA_SECRET_KEY:-6LemLRwuAAAAAJh0oU5U_cUzPXw0Mo1lUxJGuiVc}"

if grep -q "^RECAPTCHA_SITE_KEY=" .env; then
    sed -i "s|^RECAPTCHA_SITE_KEY=.*|RECAPTCHA_SITE_KEY=$CURRENT_RECAPTCHA_SITE|" .env
else
    echo "RECAPTCHA_SITE_KEY=$CURRENT_RECAPTCHA_SITE" >> .env
fi

if grep -q "^RECAPTCHA_SECRET_KEY=" .env; then
    sed -i "s|^RECAPTCHA_SECRET_KEY=.*|RECAPTCHA_SECRET_KEY=$CURRENT_RECAPTCHA_SECRET|" .env
else
    echo "RECAPTCHA_SECRET_KEY=$CURRENT_RECAPTCHA_SECRET" >> .env
fi

# Ensure Mailer and Resend settings are configured in .env (supports Render environment variable overrides)
CURRENT_MAIL_MAILER="${MAIL_MAILER:-resend_http}"
CURRENT_MAIL_HOST="${MAIL_HOST:-smtp.gmail.com}"
CURRENT_MAIL_PORT="${MAIL_PORT:-587}"
CURRENT_MAIL_USERNAME="${MAIL_USERNAME:-cogoneast.system@gmail.com}"
CURRENT_MAIL_PASSWORD="${MAIL_PASSWORD:-jydkmltxbnpnbfkx}"
CURRENT_MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-tls}"
CURRENT_MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-cogoneast.system@gmail.com}"
CURRENT_MAIL_FROM_NAME="${MAIL_FROM_NAME:-E-Barangay Cogon East}"
CURRENT_MAIL_TIMEOUT="${MAIL_TIMEOUT:-10}"
CURRENT_RESEND_KEY="${RESEND_API_KEY:-${RESEND_KEY:-${RESEND_TOKEN:-}}}"
CURRENT_RESEND_FROM="${RESEND_FROM_ADDRESS:-onboarding@resend.dev}"

for var in MAIL_MAILER:$CURRENT_MAIL_MAILER \
           MAIL_HOST:$CURRENT_MAIL_HOST \
           MAIL_PORT:$CURRENT_MAIL_PORT \
           MAIL_USERNAME:$CURRENT_MAIL_USERNAME \
           MAIL_PASSWORD:$CURRENT_MAIL_PASSWORD \
           MAIL_ENCRYPTION:$CURRENT_MAIL_ENCRYPTION \
           MAIL_FROM_ADDRESS:$CURRENT_MAIL_FROM_ADDRESS \
           MAIL_TIMEOUT:$CURRENT_MAIL_TIMEOUT \
           RESEND_API_KEY:$CURRENT_RESEND_KEY \
           RESEND_FROM_ADDRESS:$CURRENT_RESEND_FROM; do
    key="${var%%:*}"
    val="${var#*:}"
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${val}|" .env
    else
        echo "${key}=${val}" >> .env
    fi
done

if grep -q "^MAIL_FROM_NAME=" .env; then
    sed -i "s|^MAIL_FROM_NAME=.*|MAIL_FROM_NAME=\"${CURRENT_MAIL_FROM_NAME}\"|" .env
else
    echo "MAIL_FROM_NAME=\"${CURRENT_MAIL_FROM_NAME}\"" >> .env
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
else
    echo "==> Using database connection: $CURRENT_DB_CONN ($DB_HOST:$DB_PORT / $DB_DATABASE)"
fi

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

# Ensure log file exists and web server has full ownership of all writable directories
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache /var/www/html/database
chmod -R 777 storage/framework/sessions storage/framework/views storage/framework/cache
chmod -R 775 storage bootstrap/cache /var/www/html/database
chmod 664 storage/logs/laravel.log
if [ -f "/var/www/html/database/database.sqlite" ]; then
    chmod 664 /var/www/html/database/database.sqlite
fi

echo "==> Starting Apache server on port $PORT..."
exec apache2-foreground