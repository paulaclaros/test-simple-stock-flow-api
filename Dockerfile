FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    git \
    curl \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    bash

RUN docker-php-ext-install pdo pdo_pgsql pgsql bcmath zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize

RUN mkdir -p storage/app/public/media \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache && \
    chmod +x docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["./docker-entrypoint.sh"]
