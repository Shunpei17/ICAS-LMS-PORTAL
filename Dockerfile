# ============================================================
# Multi-stage Dockerfile for Laravel (PHP 8.2 + Nginx + MySQL)
# Optimized for Railway deployment
# ============================================================

# ---- Stage 1: Build frontend assets ----
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy only package files first for caching
COPY package.json package-lock.json ./

# Use --max-old-space-size to prevent OOM on Railway
ENV NODE_OPTIONS="--max-old-space-size=512"
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ---- Stage 2: Install PHP dependencies ----
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# Use --ignore-platform-reqs because the composer image doesn't have
# gd/zip extensions — they'll be available in the final runtime image
RUN composer install \
    --ignore-platform-reqs \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ---- Stage 3: Production image ----
FROM php:8.2-fpm-bookworm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini /usr/local/etc/php/conf.d/99-railway.ini

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy vendor from composer stage
COPY --from=vendor /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=frontend /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create storage link and necessary directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/app/public \
    && mkdir -p storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy and set entrypoint — fix Windows line endings for Linux
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
