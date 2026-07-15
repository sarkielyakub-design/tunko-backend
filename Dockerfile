# -----------------------------
# Stage 1 - Composer
# -----------------------------
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --optimize


# -----------------------------
# Stage 2 - PHP
# -----------------------------
FROM php:8.5-fpm

WORKDIR /var/www/html

# Install Linux packages
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    supervisor \
    cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        intl \
        bcmath \
        exif \
        gd \
        zip \
        opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Copy application
COPY --from=composer /app /var/www/html

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan migrate --force && \
    php artisan storage:link || true && \
    php artisan serve --host=0.0.0.0 --port=8000