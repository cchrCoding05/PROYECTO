# PROYECTO

Proyecto Final del Módulo  
Este proyecto cuenta con un backend en **Symfony** y un frontend en **React**. Implementa una arquitectura moderna para construir aplicaciones web completas.

---

# HelpEx

HelpEx es una plataforma innovadora diseñada para conectar a personas que necesitan ayuda con voluntarios dispuestos a ofrecer su tiempo y habilidades. La aplicación facilita la creación de una comunidad solidaria donde los usuarios pueden publicar solicitudes de ayuda, ofrecer sus servicios como voluntarios y coordinar actividades de apoyo mutuo. Con un enfoque en la accesibilidad y la facilidad de uso, HelpEx busca fomentar la colaboración y el apoyo comunitario en tiempos de necesidad.

---

## 🚀 Tecnologías Utilizadas

### Backend
- PHP 8.x
- Symfony
- Doctrine ORM
- MySQL

### Frontend
- React 18+
- Vite
- React Router
- Bootstrap

---

## 🧰 Requisitos Previos

- [PHP 8.1+](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/download/)
- [Symfony CLI](https://symfony.com/download)
- [Node.js (v18+ recomendado)](https://nodejs.org/)
- [MySQL](https://www.mysql.com/)

---

## 🛠️ Instalación del Proyecto

### 🔧 Clonar el repositorio

```bash
git clone https://github.com/cchrCoding05/PROYECTO.git
cd PROYECTO
git checkout dev
```

---

## ⚙️ Backend (Symfony)

### 1. Instalar dependencias con Composer

```bash
cd backend
composer install
```

> Esto instalará todas las dependencias definidas en `composer.json`.

### 2. Modificar archivo `.env`

Asegúrate de configurar correctamente el acceso a la base de datos.  
- Nombre de usuario en BBDD
- Contraseña
- Version de MySQL Correcta
Ejemplo:

```
DATABASE_URL="mysql://root:12345678@localhost:3306/helpex?8.0.42-0ubuntu0.24.04.1&charset=utf8mb4"
```

### 3. Comandos de Symfony para base de datos

```bash
# Crear base de datos
symfony console doctrine:database:create

# Crear migraciones
symfony console make:migration

# Ejecutar las migraciones
symfony console doctrine:migrations:migrate

# Eliminar el esquema actual (opcional)
symfony console doctrine:schema:drop --force

# Actualizar el esquema directamente (opcional)
symfony console doctrine:schema:update --force

# Cargar fixtures (datos de prueba)
symfony console doctrine:fixtures:load -n
```

---

## 🌐 Frontend (React)

### 1. Instalar dependencias de Node.js

```bash
cd ../frontend
npm install
```

> Asegúrate de tener Node.js y NPM instalados antes de ejecutar este comando.

### 2. Ejecutar la app en desarrollo

```bash
npm run dev
```

> Esto levantará la aplicación React en [http://localhost:5173](http://localhost:5173) por defecto.

---

## 👨‍💻 Autores

- Chahine Chrayeh El Mokhtari - [Tu contacto o perfil de GitHub]

---
