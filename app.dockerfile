FROM php:7.1.9-fpm

# Install any custom system requirements here
RUN apt-get update && apt-get install -y libmcrypt-dev zip unzip git mysql-client \
    && docker-php-ext-install mcrypt pdo_mysql

WORKDIR /var/www