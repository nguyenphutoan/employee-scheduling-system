# Sử dụng PHP 8.2 với Apache (Web server phổ biến nhất)
FROM php:8.2-apache

# 1. Cài đặt các thư viện cần thiết cho Laravel và Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql bcmath zip

# 2. Bật mod_rewrite của Apache (để Laravel chạy được URL đẹp)
RUN a2enmod rewrite

# 3. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Thiết lập thư mục làm việc
WORKDIR /var/www/html

# 5. Copy toàn bộ code vào trong Docker
COPY . .

# 6. Cài đặt các gói thư viện Laravel (bỏ qua dev)
RUN composer install --no-dev --optimize-autoloader

# 7. Phân quyền cho thư mục storage và cache (Quan trọng!)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Cấu hình Apache để trỏ vào thư mục public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 9. Cấu hình Port cho Render (Render dùng port 80 hoặc 10000)
# Apache mặc định chạy port 80, Render sẽ tự nhận diện.
EXPOSE 80

# 10. Lệnh chạy khi khởi động (Chạy migration + Bật server)
CMD php artisan migrate --force && apache2-foreground