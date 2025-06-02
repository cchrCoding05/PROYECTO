<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Repository\UsuarioRepository;
use App\Repository\ValoracionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/ratings', name: 'api_ratings_')]
class RatingController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private ValoracionRepository $ratingRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    #[Route('', name: 'create_rating', methods: ['POST'])]
    public function createRating(Request $request): JsonResponse
    {
        try {
            $this->logger->info('Iniciando creación de valoración');
            
            if (!$this->getUser()) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $data = json_decode($request->getContent(), true);
            $this->logger->info('Datos recibidos', ['data' => $data]);

            if (!isset($data['professional_id']) || !isset($data['rating'])) {
                $this->logger->warning('Datos incompletos', ['data' => $data]);
                return $this->json([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ], Response::HTTP_BAD_REQUEST);
            }

            $profesional = $this->usuarioRepository->find($data['professional_id']);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $data['professional_id']]);
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si ya existe una valoración
            $existingRating = $this->em->getRepository(Valoracion::class)->findOneBy([
                'usuario' => $this->getUser(),
                'profesional' => $profesional
            ]);

            if ($existingRating) {
                $this->logger->warning('Valoración ya existe', [
                    'usuario' => $this->getUser()->getId_usuario(),
                    'profesional' => $profesional->getId_usuario()
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'Ya has valorado a este profesional'
                ], Response::HTTP_BAD_REQUEST);
            }

            $valoracion = new Valoracion();
            $valoracion->setUsuario($this->getUser());
            $valoracion->setProfesional($profesional);
            $valoracion->setPuntuacion($data['rating']);
            $valoracion->setComentario($data['comment'] ?? null);
            $valoracion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($valoracion);
            $this->em->flush();

            $this->logger->info('Valoración creada exitosamente', [
                'id' => $valoracion->getId_valoracion(),
                'usuario' => $this->getUser()->getId_usuario(),
                'profesional' => $profesional->getId_usuario()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Valoración creada exitosamente',
                'data' => [
                    'id' => $valoracion->getId_valoracion(),
                    'rating' => $valoracion->getPuntuacion(),
                    'comment' => $valoracion->getComentario(),
                    'created_at' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al crear valoración', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al crear la valoración',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/professional/{id}', name: 'get_professional_ratings', methods: ['GET'])]
    public function getProfessionalRatings(int $id): JsonResponse
    {
        try {
            $professional = $this->usuarioRepository->find($id);
            if (!$professional) {
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $ratings = $this->ratingRepository->findBy(['profesional' => $professional], ['fechaCreacion' => 'DESC']);

            $data = array_map(function($rating) {
                return [
                    'id' => $rating->getId_valoracion(),
                    'rating' => $rating->getPuntuacion(),
                    'comment' => $rating->getComentario(),
                    'created_at' => $rating->getFechaCreacion()->format('c'),
                    'user' => [
                        'id' => $rating->getUsuario()->getId_usuario(),
                        'name' => $rating->getUsuario()->getNombreUsuario()
                    ]
                ];
            }, $ratings);

            return $this->json([
                'success' => true,
                'data' => [
                    'professional' => [
                        'id' => $professional->getId_usuario(),
                        'name' => $professional->getNombreUsuario(),
                        'rating' => $professional->getValoracionPromedio()
                    ],
                    'ratings' => $data
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener valoraciones: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener las valoraciones',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 