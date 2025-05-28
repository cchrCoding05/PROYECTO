FROM php:8.2-cli

# Instalar dependencias del sistema necesarias para extensiones PHP y Composer
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

# Instalar dependencias composer
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
