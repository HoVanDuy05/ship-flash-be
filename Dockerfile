FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
  postgresql-dev \
  curl \
  zip \
  unzip \
  git \
  && docker-php-ext-install pdo_pgsql

# Install Composer properly
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && chmod +x /usr/local/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Set permissions
RUN mkdir -p storage/logs bootstrap/cache \
  && chown -R www-data:www-data storage \
  && chown -R www-data:www-data bootstrap/cache \
  && chmod -R 775 storage \
  && chmod -R 775 bootstrap/cache

# Note: Cache will be cleared at runtime when container starts
# Caching requires proper .env and database connection

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]

# Add health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:9000/ping || exit 1
