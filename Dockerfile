
FROM php:8.5-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY public/ /var/www/html/
COPY includes/ /var/www/html/includes/

EXPOSE 80
