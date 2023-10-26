FROM composer:lts as composer

FROM php:8.2-apache

# Copy in composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy in the tool and config file
COPY . /var/www/html/
COPY config.php.pub-docker /var/www/html/config.php.conf
WORKDIR /var/www/html/

# Install dependencies
RUN composer require kevinoo/phpwhois:^6.3
RUN composer require maxmind-db/reader:~1.0
