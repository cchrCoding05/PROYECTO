<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    // Obtener chat con profesional
    #[Route('/professional-chat/{id}', name: 'professional_chat', methods: ['GET'])]
    public function getProfessionalChat(int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando getProfessionalChat', ['id' => $id]);
            
            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Obtener todas las valoraciones del profesional
            $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $profesional]);
            $valoracionesArray = [];
            $sumaPuntuaciones = 0;
            
            foreach ($valoraciones as $valoracion) {
                $valoracionesArray[] = [
                    'id' => $valoracion->getId_valoracion(),
                    'puntuacion' => $valoracion->getPuntuacion(),
                    'comentario' => $valoracion->getComentario(),
                    'fecha' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'usuario' => [
                        'id' => $valoracion->getUsuario()->getId_usuario(),
                        'name' => $valoracion->getUsuario()->getNombreUsuario()
                    ]
                ];
                $sumaPuntuaciones += $valoracion->getPuntuacion();
            }

            $mediaValoraciones = count($valoraciones) > 0 ? $sumaPuntuaciones / count($valoraciones) : 0;

            $result = [
                'success' => true,
                'data' => [
                    'professional' => [
                        'id' => $profesional->getId_usuario(),
                        'name' => $profesional->getNombreUsuario(),
                        'profession' => $profesional->getProfesion(),
                        'email' => $profesional->getCorreo(),
                        'description' => $profesional->getDescripcion(),
                        'photo' => $profesional->getFotoPerfil(),
                        'rating' => $mediaValoraciones,
                        'reviews_count' => count($valoraciones)
                    ],
                    'valoraciones' => $valoracionesArray
                ]
            ];

            $this->logger->info('Información del profesional obtenida exitosamente', ['id' => $id]);
            return $this->json($result);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener información del profesional', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener información del profesional',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}