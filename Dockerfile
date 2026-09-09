# syntax=docker/dockerfile:1

# -----------------------------------------------------------
# Stage 1: Build Frontend Assets
# -----------------------------------------------------------
FROM node:22-alpine AS assets-builder

WORKDIR /app

COPY package*.json vite.config.js ./
COPY resources/ ./resources/
COPY app/ ./app/

RUN npm ci --ignore-scripts || npm install --ignore-scripts
RUN npm run build

# -----------------------------------------------------------
# Stage 2: Production PHP-FPM + Nginx Image
# -----------------------------------------------------------
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies & web server
RUN apk add --no-cache \
    nginx \
    gettext \
    bash \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client

# Install PHP extensions using docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo_mysql \
    opcache \
    zip \
    gd \
    intl \
    bcmath \
    pcntl \
    redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Opcache for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Copy application files
COPY . /var/www/html

# Copy built frontend assets from Stage 1
COPY --from=assets-builder /app/public/build /var/www/html/public/build

# Install production PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Configure Nginx template and entrypoint
RUN mkdir -p /etc/nginx/templates
COPY .docker/cloudrun/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY .docker/cloudrun/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set directory permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cloud Run defaults to port 8080
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
