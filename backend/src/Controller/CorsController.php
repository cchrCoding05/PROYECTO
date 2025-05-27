<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CorsController extends AbstractController
{
    /**
     * @Route("/{catchall}", name="cors_preflight", methods={"OPTIONS"}, requirements={"catchall"=".+"})
     */
    public function corsPreflightAction(): Response
    {
        $response = new Response();
        $response->headers->set('Access-Control-Allow-Origin', 'http://www.helpex.com:22193');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
} 