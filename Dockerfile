FROM php:8.3-apache

# 1. FORCE-CLEAN Apache MPMs to stop the AH00534 error
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_worker.load && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# 2. Install dependencies for GD (required for Drupal 11)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 3. Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip opcache

# 4. Enable mod_rewrite for Drupal's Clean URLs
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# Set permissions for your brother's uploads
RUN chown -R www-data:www-data /var/www/html/web/sites/default/files