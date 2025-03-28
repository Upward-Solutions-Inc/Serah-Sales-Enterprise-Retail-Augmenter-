FROM php:8.1-cli
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pcntl sockets zip
WORKDIR /var/www/html
COPY . .
RUN git config --global --add safe.directory /var/www/html
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer require beyondcode/laravel-websockets:"^1.14"
RUN composer install
EXPOSE 4500 4501
CMD sh -c "php artisan websockets:serve --port=4501 & php artisan serve --host=0.0.0.0 --port=4500"