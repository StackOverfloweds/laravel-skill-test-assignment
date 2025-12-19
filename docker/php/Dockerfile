FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    sqlite sqlite-dev \
    libzip-dev \
    oniguruma-dev \
    bash \
    nodejs npm \
    build-base \
    pkgconfig \
    openssl-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite zip

# Set working directory
WORKDIR /var/www

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Fix permissions for storage, cache, database
RUN mkdir -p storage bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database
