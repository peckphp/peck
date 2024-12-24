ARG PHP=8.3
FROM php:${PHP}-cli-alpine

# Install dependencies in a single RUN command to reduce layers and use caching more effectively
RUN apk update && apk add --no-cache \
    zip \
    libzip-dev \
    icu-dev \
    git \
    aspell \
    aspell-en \
    linux-headers \
    autoconf \
    build-base && \
    docker-php-ext-install zip intl && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

# Copy composer from the official composer image (still keeps it in one layer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom xdebug configuration
COPY ./php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Set the working directory
WORKDIR /var/www/html

# Use a more efficient entrypoint for better flexibility
ENTRYPOINT ["php", "-d", "memory_limit=-1"]
