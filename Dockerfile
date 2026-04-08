FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
  git unzip libzip-dev libssl-dev libpq-dev \
  && docker-php-ext-install pdo pdo_pgsql zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan storage:link || true

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000