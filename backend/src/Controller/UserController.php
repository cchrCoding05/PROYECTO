<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function getProfile(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Obtener negociaciones como comprador
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from('App\Entity\NegociacionPrecio', 'n')
               ->where('n.comprador = :userId')
               ->setParameter('userId', $user->getId_usuario());

            $buyerNegotiations = $qb->getQuery()->getResult();

            // Obtener negociaciones como vendedor
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from('App\Entity\NegociacionPrecio', 'n')
               ->where('n.vendedor = :userId')
               ->setParameter('userId', $user->getId_usuario());

            $sellerNegotiations = $qb->getQuery()->getResult();

            // Formatear las negociaciones
            $formattedNegotiations = [
                'buyer' => array_map(function($n) {
                    $intercambio = $n->getIntercambio();
                    $objeto = $intercambio ? $intercambio->getObjeto() : null;
                    return [
                        'id' => $n->getId_negociacion(),
                        'product' => [
                            'id' => $objeto ? $objeto->getId_objeto() : null,
                            'name' => $objeto ? $objeto->getTitulo() : 'Producto no disponible'
                        ],
                        'seller' => [
                            'id' => $n->getVendedor()->getId_usuario(),
                            'name' => $n->getVendedor()->getNombreUsuario()
                        ],
                        'price' => $n->getPrecioPropuesto(),
                        'status' => $n->isAceptado() ? 'accepted' : 'pending',
                        'created_at' => $n->getFechaCreacion()->format('c')
                    ];
                }, $buyerNegotiations),
                'seller' => array_map(function($n) {
                    $intercambio = $n->getIntercambio();
                    $objeto = $intercambio ? $intercambio->getObjeto() : null;
                    return [
                        'id' => $n->getId_negociacion(),
                        'product' => [
                            'id' => $objeto ? $objeto->getId_objeto() : null,
                            'name' => $objeto ? $objeto->getTitulo() : 'Producto no disponible'
                        ],
                        'buyer' => [
                            'id' => $n->getComprador()->getId_usuario(),
                            'name' => $n->getComprador()->getNombreUsuario()
                        ],
                        'price' => $n->getPrecioPropuesto(),
                        'status' => $n->isAceptado() ? 'accepted' : 'pending',
                        'created_at' => $n->getFechaCreacion()->format('c')
                    ];
                }, $sellerNegotiations)
            ];

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $user->getId_usuario(),
                    'username' => $user->getNombreUsuario(),
                    'email' => $user->getCorreo(),
                    'credits' => $user->getCreditos(),
                    'profession' => $user->getProfesion(),
                    'rating' => $user->getValoracionPromedio(),
                    'sales' => $user->getVentasRealizadas(),
                    'profilePhoto' => $user->getFotoPerfil(),
                    'description' => $user->getDescripcion(),
                    'negotiations' => $formattedNegotiations
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en getProfile: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener el perfil',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/profile', name: 'profile_update', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['username'])) {
            $user->setNombreUsuario($data['username']);
        }
        if (isset($data['description'])) {
            $user->setDescripcion($data['description']);
        }
        if (isset($data['profession'])) {
            $user->setProfesion($data['profession']);
        }
        if (isset($data['profilePhoto'])) {
            $user->setFotoPerfil($data['profilePhoto']);
        }

        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Perfil actualizado con éxito',
            'data' => [
                'id' => $user->getId_usuario(),
                'username' => $user->getNombreUsuario(),
                'email' => $user->getCorreo(),
                'credits' => $user->getCreditos(),
                'profession' => $user->getProfesion(),
                'rating' => $user->getValoracionPromedio(),
                'sales' => $user->getVentasRealizadas(),
                'profilePhoto' => $user->getFotoPerfil(),
                'description' => $user->getDescripcion()
            ]
        ]);
    }

    #[Route('/top-rated', name: 'top_rated', methods: ['GET'])]
    public function getTopRatedUsers(): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            $currentUserId = $currentUser ? $currentUser->getId_usuario() : null;
            
            $qb = $this->usuarioRepository->createQueryBuilder('u')
                ->where('u.nombre_usuario != :admin')
                ->setParameter('admin', 'ADMIN');
            
            if ($currentUserId) {
                $qb->andWhere('u.id_usuario != :currentUserId')
                   ->setParameter('currentUserId', $currentUserId);
            }
            
            $qb->orderBy('u.valoracion_promedio', 'DESC')
               ->setMaxResults(10);
            
            $topUsers = $qb->getQuery()->getResult();
            
            $usersData = array_map(function($user) {
                $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $user]);
                $sumaPuntuaciones = 0;
                foreach ($valoraciones as $valoracion) {
                    $sumaPuntuaciones += $valoracion->getPuntuacion();
                }
                $mediaValoraciones = count($valoraciones) > 0 ? $sumaPuntuaciones / count($valoraciones) : 0;

                return [
                    'id' => $user->getId_usuario(),
                    'name' => $user->getNombreUsuario(),
                    'profession' => $user->getProfesion(),
                    'email' => $user->getCorreo(),
                    'description' => $user->getDescripcion(),
                    'photo' => $user->getFotoPerfil(),
                    'rating' => $mediaValoraciones,
                    'reviews_count' => count($valoraciones)
                ];
            }, $topUsers);

            return $this->json([
                'success' => true,
                'data' => $usersData
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en getTopRatedUsers: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener usuarios mejor valorados',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}