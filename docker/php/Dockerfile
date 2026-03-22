FROM php:8.2-apache

# Apache設定
RUN a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/apache2.conf

# 必要パッケージ
RUN apt-get update \
    && apt-get install -y \
        git \
        zip \
        unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP拡張
RUN docker-php-ext-install pdo pdo_mysql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP設定
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# 作業ディレクトリ
WORKDIR /var/www
