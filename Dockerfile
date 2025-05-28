# Usa imagen oficial PHP con CLI y servidor embebido
FROM php:8.2-cli

# Instalar unzip y curl para composer
RUN apt-get update && apt-get install -y unzip curl

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar el backend
WORKDIR /app/backend
COPY backend/ /app/backend/

# Instalar dependencias composer sin dev y optimizado
RUN composer install --no-dev --optimize-autoloader

# Exponer puerto (Railway usará $PORT)
EXPOSE 8000

# Comando para iniciar el servidor PHP embebido
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
