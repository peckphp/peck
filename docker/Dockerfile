ARG PHP=8.3
FROM php:${PHP}-cli-alpine

RUN apk update && apk add \
    zip libzip-dev icu-dev curl git unzip openssl hunspell hunspell-en-us aspell aspell-en

RUN docker-php-ext-install zip intl

RUN apk add --no-cache linux-headers autoconf build-base
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/99-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/99-xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENTRYPOINT ["php"]