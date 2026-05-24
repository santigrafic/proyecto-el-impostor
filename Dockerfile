FROM php:8.2-cli

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libpq-dev

# Extensiones PHP necesarias para Laravel
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar proyecto
COPY . .

RUN rm -rf bootstrap/cache/*.php

# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Permisos Laravel
RUN chmod -R 775 storage bootstrap/cache

# Puerto Render
EXPOSE 10000

# Arranque del servidor
CMD rm -rf bootstrap/cache/* && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan optimize:clear && \
    php artisan serve --host=0.0.0.0 --port=$PORT

ENV COMPOSER_ALLOW_SUPERUSER=1
