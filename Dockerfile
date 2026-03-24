FROM php:8.2-apache-bullseye

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www

COPY public/ /var/www/html/
COPY src/ /var/www/src/
COPY config/ /var/www/config/

EXPOSE 80