
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
COPY webpack.config.js ./

RUN npm install

COPY assets/ ./assets/

RUN npm run build

FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql intl opcache zip

RUN a2enmod rewrite

COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY . .

RUN touch .env

COPY --from=frontend /app/public/build public/build

RUN composer dump-autoload --optimize --no-interaction
RUN APP_ENV=prod DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console cache:warmup --env=prod || true

RUN mkdir -p var/cache var/log && chown -R www-data:www-data var/

EXPOSE 8080

CMD rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && sed -i "s/\${PORT}/${PORT:-8080}/g" /etc/apache2/sites-available/000-default.conf \
    && echo "Listen ${PORT:-8080}" > /etc/apache2/ports.conf \
    && php bin/console doctrine:schema:update --force --no-interaction \
    && apache2-foreground
