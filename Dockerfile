FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN docker-php-ext-install pdo_pgsql

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage/logs

# Install composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Clear caches
RUN php artisan config:clear
RUN php artisan cache:clear
RUN php artisan route:clear
RUN php artisan view:clear

# Expose port
EXPOSE 8000

# Start Apache server
CMD ["php-fpm"]

# Add health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:8000/api/health || exit 1
