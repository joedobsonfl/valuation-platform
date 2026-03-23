FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY public/ /var/www/html/

EXPOSE 80