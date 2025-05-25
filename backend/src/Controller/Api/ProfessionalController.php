<?php

namespace App\Controller\Api;

use App\Entity\Mensaje;
use App\Repository\MensajeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ProfessionalController extends AbstractController
{
    private $entityManager;
    private $mensajeRepository;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        MensajeRepository $mensajeRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->mensajeRepository = $mensajeRepository;
        $this->logger = $logger;
    }

    #[Route('/{mensajeId}/chat/accept-proposal/{proposalId}', name: 'accept_proposal', methods: ['POST'])]
    public function acceptProposal(int $mensajeId, int $proposalId): JsonResponse
    {
        try {
            $this->logger->info('Iniciando aceptación de propuesta', [
                'mensajeId' => $mensajeId,
                'proposalId' => $proposalId
            ]);

            $mensaje = $this->mensajeRepository->find($mensajeId);
            if (!$mensaje) {
                $this->logger->error('Mensaje no encontrado', ['mensajeId' => $mensajeId]);
                return $this->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $proposal = $this->mensajeRepository->find($proposalId);
            if (!$proposal) {
                $this->logger->error('Propuesta no encontrada', ['proposalId' => $proposalId]);
                return $this->json([
                    'success' => false,
                    'message' => 'Propuesta no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->logger->info('Propuesta encontrada', [
                'proposal' => [
                    'id' => $proposal->getId_mensaje(),
                    'contenido' => $proposal->getContenido(),
                    'leido' => $proposal->isLeido()
                ]
            ]);

            // Actualizar el estado de la propuesta
            $proposal->setLeido(true);
            $this->entityManager->persist($proposal);

            $this->entityManager->flush();

            $this->logger->info('Propuesta aceptada exitosamente', [
                'mensajeId' => $mensajeId,
                'proposalId' => $proposalId
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Propuesta aceptada correctamente',
                'data' => [
                    'mensaje' => [
                        'id' => $mensaje->getId_mensaje(),
                        'contenido' => $mensaje->getContenido(),
                        'leido' => $mensaje->isLeido()
                    ],
                    'proposal' => [
                        'id' => $proposal->getId_mensaje(),
                        'contenido' => $proposal->getContenido(),
                        'leido' => $proposal->isLeido()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al aceptar propuesta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->json([
                'success' => false,
                'message' => 'Error al aceptar la propuesta: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } 
} 