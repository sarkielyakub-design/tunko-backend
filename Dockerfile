# ===========================
# Stage 1 - Composer
# ===========================
FROM composer:2 AS composer

WORKDIR /app

# Copy the entire project
COPY . .

# Install dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --ignore-platform-reqs \
    --optimize-autoloader \
    --no-scripts

# Generate optimized autoload
RUN composer dump-autoload --optimize

# Discover Laravel packages
RUN php artisan package:discover

# ===========================
# Stage 2 - PHP Runtime
# ===========================
FROM php:8.4-cli

WORKDIR /var/www/html

# Install system packages
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
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
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Redis (optional)
# RUN pecl install redis && docker-php-ext-enable redis

# Copy project from Composer stage
COPY --from=composer /app /var/www/html

# Create Laravel directories
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Permissions
RUN chmod -R 775 storage bootstrap/cache

# Laravel environment
ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

CMD php artisan migrate --force && \
    php artisan storage:link || true && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}