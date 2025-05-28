FROM php:8.2-cli

# Instalar dependencias necesarias para extensiones PHP
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install zip mbstring pdo pdo_mysql

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar backend
WORKDIR /app/backend
COPY backend/ /app/backend/

EXPOSE 8000

# Al iniciar: instalar dependencias con Composer y luego iniciar servidor PHP embebido
CMD composer install --no-dev --verbose --no-interaction && php -S 0.0.0.0:8000 -t public
