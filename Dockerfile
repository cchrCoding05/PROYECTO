FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install zip mbstring pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app/backend
COPY backend/ /app/backend/

EXPOSE 8000

CMD sh -c "composer install --no-dev --verbose --no-interaction && php -S 0.0.0.0:$PORT -t public public/index.php"
