<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/credits', name: 'api_credits_')]
class CreditsController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    // Obtener saldo de créditos
    #[Route('/balance', name: 'balance', methods: ['GET'])]
    public function getCreditsBalance(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'balance' => $user->getCreditos()
            ]
        ]);
    }

    // Obtener historial de créditos
    #[Route('/history', name: 'history', methods: ['GET'])]
    public function getCreditsHistory(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // TODO: Implementar historial de créditos
        return $this->json([
            'success' => true,
            'data' => []
        ]);
    }

    // Transferencia de créditos
    #[Route('/transfer', name: 'transfer', methods: ['POST'])]
    public function transferCredits(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['recipient_id']) || !isset($data['amount'])) {
            return $this->json([
                'success' => false,
                'message' => 'Faltan datos obligatorios'
            ], Response::HTTP_BAD_REQUEST);
        }

        // TODO: Implementar transferencia de créditos
        return $this->json([
            'success' => true,
            'message' => 'Transferencia realizada con éxito'
        ]);
    }
}