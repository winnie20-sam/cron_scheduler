# Stage 1 - Install dependencies
FROM php:8.4-fpm-alpine AS builder

RUN apk add --no-cache curl unzip git \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --no-scripts


# Stage 2 - Lean production image
FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY --from=builder /app .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
