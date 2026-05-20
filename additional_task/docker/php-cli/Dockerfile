FROM php:8.4-cli-bullseye

RUN apt-get update && apt-get install -y \
    libpq-dev \
    wget \
    zlib1g-dev \
    libmcrypt-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    libicu-dev \
    pkg-config \
    libsqlite3-dev 

RUN docker-php-ext-install pdo pdo_sqlite zip intl

RUN pecl install xdebug-3.5.1 \
    && docker-php-ext-enable xdebug

RUN wget https://getcomposer.org/installer -O - -q | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR /app
