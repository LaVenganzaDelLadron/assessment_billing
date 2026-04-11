FROM dunglas/frankenphp:php8.4

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --optimize-autoloader --no-interaction

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
