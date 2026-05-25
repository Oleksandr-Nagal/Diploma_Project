
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

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

COPY . .

COPY --from=frontend /app/public/build public/build

RUN composer run-script auto-scripts || true

RUN mkdir -p var/cache var/log && chown -R www-data:www-data var/
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT:-8080}

CMD ["apache2-foreground"]
