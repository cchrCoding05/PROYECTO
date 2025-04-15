@echo off
echo Reiniciando la base de datos y cargando fixtures...
php bin/console app:reset-database
pause 