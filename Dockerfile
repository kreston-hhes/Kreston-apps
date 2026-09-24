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

# SALIN SEMUA FILE PROYEK TERLEBIH DAHULU (agar composer.json terbaca)
COPY . .

# Baru jalankan Composer install setelah file-file project masuk ke container
RUN composer install --no-dev --optimize-autoloader

# Berikan izin akses untuk folder storage dan bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Jalankan PHP built-in server pada port 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]