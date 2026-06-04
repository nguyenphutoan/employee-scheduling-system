FROM php:8.2-apache

# Cài đặt các thư viện hệ thống cần thiết 
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Cài đặt các extension PHP 
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Bật Apache Rewrite Module
RUN a2enmod rewrite

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Chuyển vào thư mục gốc của app
WORKDIR /var/www/html

# Copy toàn bộ code vào trong container
COPY . /var/www/html

# Tải các thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# Đổi thư mục gốc của Apache trỏ vào thư mục public của Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cấp quyền đọc ghi cho thư mục storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80