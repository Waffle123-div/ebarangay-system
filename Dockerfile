# ── Stage 1: Build Frontend Assets (Vite) ──
FROM node:20-alpine AS node-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci --prefer-offline --no-audit

COPY . .
RUN npm run build

# ── Stage 2: PHP Application Container ──
FROM php:8.2-apache

# Install system dependencies & PHP extensions required by Laravel 12 & PhpWord
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Apache VirtualHost to serve /var/www/html/public with AllowOverride All
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite headers

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy compiled Vite assets from Stage 1
COPY --from=node-builder /app/public/build ./public/build

# Copy node_modules from Stage 1 for WebSocket server (ws)
COPY --from=node-builder /app/node_modules ./node_modules

# Install production PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set directory permissions for Laravel
RUN mkdir -p /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose default HTTP port
EXPOSE 80 8080

ENTRYPOINT ["docker-entrypoint.sh"]
