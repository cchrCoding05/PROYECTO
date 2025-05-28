#!/bin/bash

# Crear directorio de sesiones si no existe
mkdir -p var/cache/sessions

# Establecer permisos correctos
chmod -R 775 var/cache
chown -R www-data:www-data var/cache

# Asegurarse de que el directorio de sesiones sea escribible
chmod 775 var/cache/sessions
chown www-data:www-data var/cache/sessions

echo "Permisos configurados correctamente" 