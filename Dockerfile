FROM php:8.2-fpm

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Salin composer.json dan composer.lock terlebih dahulu (untuk optimasi cache Docker)
COPY composer.json composer.lock ./

# Jalankan composer install sebelum menyalin seluruh file kode
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Salin seluruh sisa file proyek Laravel
COPY . .

# Selesaikan dump autoloader Composer
RUN composer dump-autoload --optimize --no-dev

# Berikan izin akses untuk folder storage dan bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Jalankan PHP built-in server yang stabil pada port 8000
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]