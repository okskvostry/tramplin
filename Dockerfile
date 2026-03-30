FROM php:8.2-apache

# Устанавливаем расширение PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Включаем mod_rewrite
RUN a2enmod rewrite

# Создаем папку для загрузок
RUN mkdir -p /var/www/html/uploads/avatars /var/www/html/uploads/logos /var/www/html/uploads/offices && \
    chmod -R 777 /var/www/html/uploads