<<<<<<< HEAD
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    gd \
    zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=node:22 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /var/www

# SALIN SELURUH PROJECT DI AWAL 
# (Agar file artisan, routes, dan config lengkap sebelum composer install & npm build)
COPY . .

# Jalankan composer install
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev

# Jalankan npm dependencies & build Tailwind
RUN npm ci
RUN npm run build

# Bersihkan cache artisan
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true

RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
=======
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
>>>>>>> c31107457c5142baa723e2803750cd532a04517f
