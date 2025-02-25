FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libxml2 \
    wget \
    unzip \
    git \
    curl && \
    docker-php-ext-install pdo_mysql zip gd exif && \
    docker-php-ext-enable gd && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy application files
COPY . /var/www
RUN chown -R www-data:www-data /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache


# Expose port
EXPOSE 9000
CMD ["php-fpm"]
