FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev supervisor  # ← supervisor here

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/korva
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-scripts

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000
CMD ["php-fpm"]