<?php

namespace App\Controller;

use App\Security\SecurityBundleStub;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controlador temporal para manejar las funciones de autenticación
 * mientras se resuelven los problemas con el SecurityBundle.
 * 
 * NOTA: Este controlador está DESACTIVADO temporalmente para permitir
 * que el ApiController maneje las rutas de autenticación.
 */
class AuthController extends AbstractController
{
    // Rutas desactivadas temporalmente
    
    /*
    #[Route('/api/login_check', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Extraer credenciales del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);
        
        // Mensaje temporal
        return new JsonResponse([
            'message' => 'El sistema de autenticación está temporalmente deshabilitado.',
            'info' => 'SecurityBundle no está disponible. Por favor, resuelve los problemas de instalación.'
        ], 503); // 503 Service Unavailable
    }
    
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // Extraer datos del usuario del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);
        
        // Mensaje temporal
        return new JsonResponse([
            'message' => 'El sistema de registro está temporalmente deshabilitado.',
            'info' => 'SecurityBundle no está disponible. Por favor, resuelve los problemas de instalación.'
        ], 503); // 503 Service Unavailable
    }
    
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Mensaje temporal
        return new JsonResponse([
            'message' => 'El cierre de sesión está temporalmente deshabilitado.',
            'info' => 'SecurityBundle no está disponible. Por favor, resuelve los problemas de instalación.'
        ], 503); // 503 Service Unavailable
    }
    
    #[Route('/api/user/current', name: 'api_current_user', methods: ['GET'])]
    public function getCurrentUser(): JsonResponse
    {
        // Devolvemos null para indicar que no hay usuario autenticado
        return new JsonResponse(null);
    }
    */

    // Nuevo método para redirigir a ApiController
    #[Route('/api/auth/status', name: 'auth_status', methods: ['GET'])]
    public function authStatus(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'AuthController está desactivado. Las rutas de autenticación son manejadas por ApiController.'
        ]);
    }
} 