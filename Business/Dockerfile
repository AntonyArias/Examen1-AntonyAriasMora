FROM php:8.5-apache
WORKDIR /var/www/html

RUN a2enmod rewrite
RUN apt-get update && apt-get install -y unzip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY ./Business/composer.json .
COPY ./Business/.env .

RUN composer install --no-dev --no-interaction

RUN sed -i 's/DocumentRoot.*$/DocumentRoot \/var\/www\/html\/public/' /etc/apache2/sites-enabled/000-default.conf
