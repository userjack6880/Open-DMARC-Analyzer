FROM composer:lts as composer

FROM php:8.2-apache

# Install dependencies
RUN apt-get update -y \
    && apt-get install -y git

# Copy in composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy in the tool and config file
COPY . /var/www/html/
COPY config.php.pub-docker /var/www/html/config.php
WORKDIR /var/www/html/

# Install dependencies
RUN composer require kevinoo/phpwhois:^6.3
RUN composer require maxmind-db/reader:~1.0
