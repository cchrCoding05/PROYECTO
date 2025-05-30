<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Entity\NegociacionPrecio;
use App\Entity\NegociacionServicio;
use App\Entity\Notificacion;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/professionals', name: 'api_professionals_')]
class ProfessionalController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function searchProfessionals(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('query', '');
            $this->logger->info('Buscando profesionales', ['query' => $query]);

            $currentUser = $this->getUser();
            $currentUserId = $currentUser ? $currentUser->getId_usuario() : null;

            $qb = $this->em->createQueryBuilder();
            $qb->select('u')
               ->from(Usuario::class, 'u')
               ->where('u.profesion IS NOT NULL')
               ->andWhere('u.nombre_usuario != :admin')
               ->setParameter('admin', 'ADMIN');

            if ($currentUserId) {
                $qb->andWhere('u.id_usuario != :currentUserId')
                   ->setParameter('currentUserId', $currentUserId);
            }

            if (!empty($query)) {
                $normalizedQuery = $this->normalizeText($query);
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(u.nombre_usuario)', ':query'),
                        $qb->expr()->like('LOWER(u.profesion)', ':query')
                    )
                )
                ->setParameter('query', '%' . $normalizedQuery . '%');
            }

            $professionals = $qb->getQuery()->getResult();
            
            $result = [];
            foreach ($professionals as $professional) {
                // Obtener valoraciones del profesional
                $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $professional]);
                $sumaPuntuaciones = 0;
                foreach ($valoraciones as $valoracion) {
                    $sumaPuntuaciones += $valoracion->getPuntuacion();
                }
                $mediaValoraciones = count($valoraciones) > 0 ? $sumaPuntuaciones / count($valoraciones) : 0;

                $result[] = [
                    'id' => $professional->getId_usuario(),
                    'name' => $professional->getNombreUsuario(),
                    'profession' => $professional->getProfesion(),
                    'email' => $professional->getCorreo(),
                    'description' => $professional->getDescripcion(),
                    'photo' => $professional->getFotoPerfil(),
                    'rating' => $mediaValoraciones,
                    'reviews_count' => count($valoraciones)
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error en la búsqueda de profesionales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al buscar profesionales',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getProfessional(string $id): JsonResponse
    {
        try {
            $professionalId = (int) $id;
            if ($professionalId <= 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'ID de profesional inválido'
                ], Response::HTTP_BAD_REQUEST);
            }

            $professional = $this->usuarioRepository->find($professionalId);
            
            if (!$professional) {
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $professional->getId_usuario(),
                    'name' => $professional->getNombreUsuario(),
                    'profession' => $professional->getProfesion(),
                    'rating' => $professional->getValoracionPromedio(),
                    'reviews_count' => $professional->getValoraciones()->count(),
                    'description' => $professional->getDescripcion(),
                    'photo' => $professional->getFotoPerfil()
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener profesional', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener el profesional',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/ratings', name: 'ratings', methods: ['GET'])]
    public function getProfessionalRatings(int $id): JsonResponse
    {
        $professional = $this->usuarioRepository->find($id);
        
        if (!$professional) {
            return $this->json([
                'success' => false,
                'message' => 'Profesional no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $ratings = $professional->getValoraciones();
        
        $data = array_map(function($valoracion) {
            return [
                'id' => $valoracion->getId_valoracion(),
                'user' => [
                    'id' => $valoracion->getUsuario()->getId_usuario(),
                    'name' => $valoracion->getUsuario()->getNombreUsuario()
                ],
                'rating' => $valoracion->getPuntuacion(),
                'comment' => $valoracion->getComentario(),
                'created_at' => $valoracion->getFechaCreacion()->format('c')
            ];
        }, $ratings->toArray());

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function normalizeText(string $text): string
    {
        return strtolower(trim($text));
    }
}
