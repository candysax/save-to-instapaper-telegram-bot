FROM php:8.2-fpm

RUN mkdir -p /var/www/
WORKDIR /var/www/

COPY . /var/www/
COPY --from=composer:2.6 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update
RUN apt-get install libzip-dev -y
RUN docker-php-ext-configure zip \
  && docker-php-ext-install zip

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install --no-dev

RUN php webhook.php
