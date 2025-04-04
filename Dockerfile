FROM php:8.1-cli

# Install nano for in-container editing
RUN apt-get update && apt-get install -y nano

# Copy your custom my.cnf (custom.cnf should be in the same folder)
COPY custom.cnf /etc/mysql/conf.d/custom.cnf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring gd zip intl

# Set working directory
WORKDIR /var/www

# Copy environment file
COPY .env .env
COPY . .

# Install Laravel websockets and project dependencies
RUN composer require beyondcode/laravel-websockets:"^1.14" \
    && composer install --no-scripts --no-interaction --optimize-autoloader

# Set permissions and create storage symlink
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link

# Expose necessary ports
EXPOSE 8000 6001

# Run Laravel + WebSockets with safe storage link
CMD sh -c "php artisan websockets:serve --port=6001 & php artisan serve --host=0.0.0.0 --port=8000"

