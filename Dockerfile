# Sử dụng PHP 8.2 với Apache
FROM php:8.2-apache

# 1. Cài đặt thư viện
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql bcmath zip

# 2. Bật mod_rewrite
RUN a2enmod rewrite

# 3. Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Thiết lập thư mục làm việc
WORKDIR /var/www/html

# 5. Copy code
COPY . .

# 6. Cài gói thư viện
RUN composer install --no-dev --optimize-autoloader

# 7. Phân quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. CẤU HÌNH APACHE =================================
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Đổi thư mục gốc về /public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Cho phép Apache đọc file .htaccess để Laravel điều hướng URL
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf
# =======================================================================

EXPOSE 80

CMD php artisan migrate --force && apache2-foreground