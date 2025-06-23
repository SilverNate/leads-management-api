FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpq \
    build-base \
    zip \
    unzip \
    git \
    nginx \
    libzip-dev

# Install PHP extensions required by Laravel and PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /tmp/pear \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy application code
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]