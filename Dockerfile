# Use PHP 8.3 with Apache for Drupal 11
FROM php:8.3-apache

# Install the GD extension and other system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip opcache

# Enable Apache mod_rewrite for Drupal's clean URLs
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy all project files into the container
COPY . .

# Set permissions for the files directory
RUN chown -R www-data:www-data /var/www/html/web/sites/default/files