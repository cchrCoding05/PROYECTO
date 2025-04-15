<?php

namespace App\Security;

/**
 * Clase stub temporal para reemplazar la funcionalidad del SecurityBundle
 * mientras se resuelven los problemas de instalación.
 * 
 * Esta clase debe ser eliminada una vez que se pueda instalar correctamente
 * el paquete symfony/security-bundle.
 */
class SecurityBundleStub 
{
    /**
     * Método stub para comprobar si hay un usuario autenticado
     */
    public static function isAuthenticated(): bool
    {
        // Por defecto, asumimos que no hay usuario autenticado
        return false;
    }

    /**
     * Método stub para obtener un usuario ficticio
     */
    public static function getCurrentUser(): ?object
    {
        // Devuelve null para indicar que no hay usuario
        return null;
    }

    /**
     * Método stub para simular un proceso de inicio de sesión
     */
    public static function login(array $credentials): array
    {
        // Devuelve un resultado de éxito falso
        return [
            'success' => false,
            'message' => 'Función de inicio de sesión no disponible. El SecurityBundle no está instalado.'
        ];
    }

    /**
     * Método stub para simular un proceso de registro
     */
    public static function register(array $userData): array
    {
        // Devuelve un resultado de éxito falso
        return [
            'success' => false,
            'message' => 'Función de registro no disponible. El SecurityBundle no está instalado.'
        ];
    }

    /**
     * Método stub para simular un proceso de cierre de sesión
     */
    public static function logout(): array
    {
        // Devuelve un resultado de éxito falso
        return [
            'success' => false,
            'message' => 'Función de cierre de sesión no disponible. El SecurityBundle no está instalado.'
        ];
    }
} 