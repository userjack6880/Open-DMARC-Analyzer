FROM composer:lts as composer

FROM php:8.2-apache

# Install package dependencies
RUN apt-get update -y \
    && apt-get install -y git libpq-dev

# Copy in composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy in the tool and config file
COPY . /var/www/html/
COPY config.php.pub-docker /var/www/html/config.php
WORKDIR /var/www/html/

# Install php dependencies
RUN composer require kevinoo/phpwhois:^6.3
RUN composer require maxmind-db/reader:~1.0

# Enable php modules
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Setup to use the stock php production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
