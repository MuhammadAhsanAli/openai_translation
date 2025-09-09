# PHP 8.2 FPM image
FROM php:8.2-fpm AS base

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libonig-dev \
    libzip-dev \
    zip \
    netcat-openbsd \
    libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Build stage
FROM base AS build

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-interaction --optimize-autoloader --prefer-dist --no-scripts

# Runtime image
FROM base AS final

WORKDIR /var/www

# Copy necessary files
COPY . .
COPY --from=build /var/www/vendor ./vendor

RUN composer run-script post-autoload-dump

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Ensure PHP-FPM listens
RUN sed -i 's|^listen = .*|listen = 0.0.0.0:9000|' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
