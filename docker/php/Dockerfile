ARG APP_ENV=dev

FROM php:8.4-fpm AS base

ARG APP_ENV=dev

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        curl \
        fontconfig \
        libfreetype6 \
        libjpeg62-turbo \
        libpng16-16 \
        libssl3 \
        libx11-6 \
        libxcb1 \
        libxext6 \
        libxrender1 \
        xfonts-75dpi \
        xfonts-base \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        zip \
        opcache \
        gd \
    && curl -fsSL https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb -o /tmp/wkhtmltox.deb \
    && dpkg -i /tmp/wkhtmltox.deb \
    && rm /tmp/wkhtmltox.deb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ENV WKHTMLTOPDF_PATH=/usr/local/bin/wkhtmltopdf
ENV WKHTMLTOIMAGE_PATH=/usr/local/bin/wkhtmltoimage

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache-${APP_ENV}.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html
