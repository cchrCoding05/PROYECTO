<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CorsController extends AbstractController
{
    /**
     * @Route("/{catchall}", name="cors_preflight", methods={"OPTIONS"}, requirements={"catchall"=".+"})
     */
    public function corsPreflightAction(Request $request): Response
    {
        $origin = $request->headers->get('Origin');
        $originPattern = $_ENV['CORS_ALLOW_ORIGIN'] ?? '';

        $response = new Response();

        if ($origin && preg_match("/$originPattern/", $origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
