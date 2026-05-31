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
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql intl opcache zip

RUN a2enmod rewrite proxy proxy_http proxy_wstunnel

RUN curl -L https://github.com/dunglas/mercure/releases/download/v0.16.3/mercure_Linux_x86_64.tar.gz -o /tmp/mercure.tar.gz \
    && cd /tmp && tar xzf mercure.tar.gz \
    && mv /tmp/mercure /usr/local/bin/mercure \
    && chmod +x /usr/local/bin/mercure \
    && rm /tmp/mercure.tar.gz

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

COPY .docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]