#!/bin/bash

# Actualizar el sistema
sudo apt update
sudo apt upgrade -y

# Instalar Apache y PHP
sudo apt install -y apache2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl

# Habilitar módulos de Apache necesarios
sudo a2enmod proxy_fcgi setenvif rewrite headers expires deflate ssl

# Configurar PHP-FPM
sudo a2enconf php8.2-fpm

# Crear directorios
sudo mkdir -p /var/www/helpex
sudo chown -R www-data:www-data /var/www/helpex

# Copiar configuraciones de Apache
sudo cp backend/apache-config.conf /etc/apache2/sites-available/api.helpex.com.conf
sudo cp frontend/apache-config.conf /etc/apache2/sites-available/www.helpex.com.conf

# Habilitar sitios
sudo a2ensite api.helpex.com.conf
sudo a2ensite www.helpex.com.conf

# Deshabilitar sitio por defecto
sudo a2dissite 000-default.conf

# Configurar permisos para el directorio de sesiones
sudo mkdir -p /var/www/helpex/backend/var/cache/sessions
sudo chmod -R 775 /var/www/helpex/backend/var/cache
sudo chown -R www-data:www-data /var/www/helpex/backend/var/cache

# Configurar permisos para las claves JWT
sudo chown www-data:www-data /var/www/helpex/backend/config/jwt/private.pem /var/www/helpex/backend/config/jwt/public.pem
sudo chmod 644 /var/www/helpex/backend/config/jwt/private.pem /var/www/helpex/backend/config/jwt/public.pem

# Reiniciar servicios
sudo systemctl restart apache2
sudo systemctl restart php8.2-fpm

echo "Configuración del servidor completada" 