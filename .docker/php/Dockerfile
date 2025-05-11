FROM composer:2.8 AS build

WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --no-progress

FROM php:8.3-fpm-alpine

WORKDIR /app

RUN apk add --no-cache bash shadow postgresql-dev
RUN docker-php-ext-install pdo_pgsql opcache intl

COPY --from=build /build/vendor ./vendor

COPY . .

COPY .docker/php/config/php-fpm.conf /etc/php-fpm.conf

RUN useradd elephlix
RUN mkdir ./var \
    && chown -R elephlix:elephlix ./var
USER elephlix

ENV APP_ENV="prod"
ENV DATABASE_URL="postgresql://root:root@db/elephlix?serverVersion=17&charset=utf8"

EXPOSE 9000

ENTRYPOINT ["/bin/bash", ".docker/php/scripts/start.sh"]
