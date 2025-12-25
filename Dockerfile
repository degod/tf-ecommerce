# ===== STAGE 1: Build PHP extensions =====
FROM php:8.2-fpm-alpine AS php-builder

RUN apk add --no-cache \
    git curl zip unzip libpng libjpeg-turbo freetype libzip \
    oniguruma icu libxml2 postgresql-libs mysql-client \
    libc6-compat chromium \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev oniguruma-dev icu-dev libxml2-dev postgresql-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# ===== STAGE 2: Runtime =====
FROM php:8.2-fpm-alpine

# Install RUNTIME libs required by GD, intl, zip, etc.
RUN apk add --no-cache \
    libpng libjpeg-turbo freetype libzip icu libxml2 oniguruma \
    bash

# Copy PHP runtime-built extensions
COPY --from=php-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-builder /usr/local/etc/php/ /usr/local/etc/php/
COPY --from=php-builder /usr/bin/composer /usr/bin/composer

# Create user
RUN adduser -D -u 1000 -G www-data developer \
    && mkdir -p /var/www/html \
    && chown developer:www-data /var/www/html

WORKDIR /var/www/html

# Copy ONLY composer files first (cache benefit)
COPY .env.example ./
COPY composer.json ./
COPY composer.lock* ./

# Create .env from example if missing
RUN cp .env.example .env

# Install composer dependencies (now code exists)
RUN composer install --no-scripts --prefer-dist

# Copy application source code
COPY . .

# PHP config overrides
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000
CMD ["php-fpm"]