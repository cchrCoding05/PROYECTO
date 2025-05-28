<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CorsController extends AbstractController
{
    #[Route('/{catchall}', name: 'cors_preflight', methods: ['OPTIONS'], requirements: ['catchall' => '.+'])]
    public function corsPreflightAction(): Response
    {
        $response = new Response();
        
        // Orígenes permitidos
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:3000',
        ];
        
        // Agregar dominios de Railway en producción
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (strpos($origin, '.railway.app') !== false || strpos($origin, '.up.railway.app') !== false) {
            $allowedOrigins[] = $origin;
        }
        
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}