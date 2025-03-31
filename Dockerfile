FROM php:8.1-cli

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

# Copy only composer files first for caching
COPY composer.json composer.lock ./

# Install dependencies (including websockets)
RUN composer install --no-scripts --no-interaction --optimize-autoloader

# Copy Laravel project files into the container
COPY . /var/www

# Install Laravel dependencies (without running migrations)
RUN composer install --no-scripts --no-interaction --optimize-autoloader

# Ensure storage and bootstrap/cache directories are writable
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

RUN rm -rf public/storage

# Create a new storage symlink
RUN php artisan storage:link

RUN php artisan key:generate

# Generate app key AFTER the container starts
CMD php artisan serve --host=0.0.0.0 --port=8000

# Expose Laravel port
EXPOSE 8000