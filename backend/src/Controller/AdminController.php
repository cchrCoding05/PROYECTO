<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Objeto;
use App\Entity\IntercambioObjeto;
use App\Entity\NegociacionPrecio;
use App\Repository\UsuarioRepository;
use App\Repository\ObjetoRepository;
use App\Repository\NegociacionPrecioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/admin', name: 'api_admin_')]
class AdminController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private ObjetoRepository $objetoRepository,
        private NegociacionPrecioRepository $negociacionPrecioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    /**
     * Verificar si el usuario actual es administrador
     */
    private function isAdmin(): bool
    {
        $user = $this->getUser();
        return $user && $user->getNombreUsuario() === 'ADMIN';
    }

    /**
     * Respuesta de acceso no autorizado
     */
    private function unauthorizedResponse(): JsonResponse
    {
        return $this->json([
            'success' => false,
            'message' => 'Acceso no autorizado'
        ], Response::HTTP_FORBIDDEN);
    }

    // Listar usuarios
    #[Route('/users', name: 'list_users', methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $search = $request->query->get('search', '');
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
           ->from(Usuario::class, 'u')
           ->where('u.nombre_usuario != :admin')
           ->setParameter('admin', 'ADMIN');

        if ($search) {
            $qb->andWhere('u.nombre_usuario LIKE :search OR u.correo LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $users = $qb->getQuery()->getResult();

        $formattedUsers = array_map(function($user) {
            return [
                'id' => $user->getId_usuario(),
                'username' => $user->getNombreUsuario(),
                'email' => $user->getCorreo(),
                'credits' => $user->getCreditos(),
                'foto_perfil' => $user->getFotoPerfil(),
                'profession' => $user->getProfesion(),
                'description' => $user->getDescripcion(),
                'rating' => $user->getValoracionPromedio(),
                'sales' => $user->getVentasRealizadas()
            ];
        }, $users);

        return $this->json([
            'success' => true,
            'data' => $formattedUsers
        ]);
    }

    // Eliminar usuario
    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $user = $this->usuarioRepository->find($id);
            
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si el usuario tiene registros asociados
            $qb = $this->em->createQueryBuilder();
            $qb->select('COUNT(o)')
               ->from(Objeto::class, 'o')
               ->where('o.usuario = :userId')
               ->setParameter('userId', $user);
            $hasProducts = $qb->getQuery()->getSingleScalarResult() > 0;

            $hasNegotiations = $this->negociacionPrecioRepository->count(['comprador' => $user]) > 0 || 
                             $this->negociacionPrecioRepository->count(['vendedor' => $user]) > 0;

            $qb = $this->em->createQueryBuilder();
            $qb->select('COUNT(i)')
               ->from(IntercambioObjeto::class, 'i')
               ->where('i.vendedor = :userId OR i.comprador = :userId')
               ->setParameter('userId', $user);
            $hasExchanges = $qb->getQuery()->getSingleScalarResult() > 0;

            if ($hasProducts || $hasNegotiations || $hasExchanges) {
                $message = 'No se puede eliminar este usuario porque tiene ';
                
                if ($hasProducts) {
                    $message .= 'productos asociados';
                } else if ($hasNegotiations) {
                    $message .= 'negociaciones asociadas';
                } else if ($hasExchanges) {
                    $message .= 'intercambios asociados';
                }
                
                return $this->json([
                    'success' => false,
                    'message' => $message
                ], Response::HTTP_CONFLICT);
            }

            $this->em->remove($user);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al eliminar usuario: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Listar productos
    #[Route('/products', name: 'list_products', methods: ['GET'])]
    public function listProducts(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $search = $request->query->get('search', '');
        $qb = $this->em->createQueryBuilder();
        $qb->select('o', 'u')
           ->from(Objeto::class, 'o')
           ->join('o.usuario', 'u');

        if ($search) {
            $qb->andWhere('o.titulo LIKE :search OR u.nombre_usuario LIKE :search OR o.creditos LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $products = $qb->getQuery()->getResult();

        $formattedProducts = array_map(function($product) {
            return [
                'id' => $product->getId_objeto(),
                'name' => $product->getTitulo(),
                'description' => $product->getDescripcion(),
                'credits' => $product->getCreditos(),
                'state' => $product->getEstado(),
                'image' => $product->getImagen(),
                'created_at' => $product->getFechaCreacion()->format('Y-m-d H:i:s'),
                'seller' => [
                    'id' => $product->getUsuario()->getId_usuario(),
                    'username' => $product->getUsuario()->getNombreUsuario()
                ]
            ];
        }, $products);

        return $this->json([
            'success' => true,
            'data' => $formattedProducts
        ]);
    }

    // Eliminar producto
    #[Route('/products/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(int $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $product = $this->objetoRepository->find($id);
        if (!$product) {
            return $this->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // Verificar si el producto está intercambiado
            if ($product->getEstado() === Objeto::ESTADO_INTERCAMBIADO) {
                return $this->json([
                    'success' => false,
                    'message' => 'No se puede eliminar este producto porque ya ha sido intercambiado'
                ], Response::HTTP_CONFLICT);
            }

            // Verificar si el producto tiene negociaciones activas
            $qb = $this->em->createQueryBuilder();
            $qb->select('COUNT(n)')
               ->from(NegociacionPrecio::class, 'n')
               ->join('n.intercambio', 'i')
               ->where('i.objeto = :productId')
               ->andWhere('n.aceptado = :aceptado')
               ->setParameter('productId', $product)
               ->setParameter('aceptado', true);
            
            $hasActiveNegotiations = $qb->getQuery()->getSingleScalarResult() > 0;

            if ($hasActiveNegotiations) {
                return $this->json([
                    'success' => false,
                    'message' => 'No se puede eliminar este producto porque tiene negociaciones activas'
                ], Response::HTTP_CONFLICT);
            }

            $this->em->remove($product);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Producto eliminado con éxito'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al eliminar producto: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Obtener estadísticas del sistema
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function getSystemStats(): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            // Contar usuarios (excluyendo admin)
            $totalUsers = $this->usuarioRepository->count(['nombre_usuario' => 'ADMIN']) > 0 
                ? $this->usuarioRepository->count([]) - 1 
                : $this->usuarioRepository->count([]);

            // Contar productos
            $totalProducts = $this->objetoRepository->count([]);

            // Contar productos por estado
            $availableProducts = $this->objetoRepository->count(['estado' => Objeto::ESTADO_DISPONIBLE]);
            $reservedProducts = $this->objetoRepository->count(['estado' => Objeto::ESTADO_RESERVADO]);
            $exchangedProducts = $this->objetoRepository->count(['estado' => Objeto::ESTADO_INTERCAMBIADO]);

            // Contar negociaciones
            $totalNegotiations = $this->negociacionPrecioRepository->count([]);
            $acceptedNegotiations = $this->negociacionPrecioRepository->count(['aceptado' => true]);

            // Obtener usuarios más activos (por número de productos)
            $qb = $this->em->createQueryBuilder();
            $qb->select('u.nombre_usuario, COUNT(o.id_objeto) as product_count')
               ->from(Usuario::class, 'u')
               ->leftJoin('u.objetos', 'o')
               ->where('u.nombre_usuario != :admin')
               ->setParameter('admin', 'ADMIN')
               ->groupBy('u.id_usuario')
               ->orderBy('product_count', 'DESC')
               ->setMaxResults(5);

            $topSellers = $qb->getQuery()->getResult();

            return $this->json([
                'success' => true,
                'data' => [
                    'users' => [
                        'total' => $totalUsers
                    ],
                    'products' => [
                        'total' => $totalProducts,
                        'available' => $availableProducts,
                        'reserved' => $reservedProducts,
                        'exchanged' => $exchangedProducts
                    ],
                    'negotiations' => [
                        'total' => $totalNegotiations,
                        'accepted' => $acceptedNegotiations,
                        'pending' => $totalNegotiations - $acceptedNegotiations
                    ],
                    'top_sellers' => $topSellers
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener estadísticas: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Obtener actividad reciente
    #[Route('/activity', name: 'activity', methods: ['GET'])]
    public function getRecentActivity(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $limit = $request->query->get('limit', 10);
            $activities = [];

            // Obtener productos recientes
            $qb = $this->objetoRepository->createQueryBuilder('o')
                ->select('o', 'u')
                ->join('o.usuario', 'u')
                ->orderBy('o.fecha_creacion', 'DESC')
                ->setMaxResults($limit);

            $recentProducts = $qb->getQuery()->getResult();

            foreach ($recentProducts as $product) {
                $activities[] = [
                    'type' => 'product_created',
                    'date' => $product->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'description' => 'Nuevo producto: ' . $product->getTitulo(),
                    'user' => $product->getUsuario()->getNombreUsuario(),
                    'details' => [
                        'product_id' => $product->getId_objeto(),
                        'product_name' => $product->getTitulo(),
                        'credits' => $product->getCreditos()
                    ]
                ];
            }

            // Obtener negociaciones recientes
            $qb = $this->negociacionPrecioRepository->createQueryBuilder('n')
                ->select('n', 'c', 'v')
                ->join('n.comprador', 'c')
                ->join('n.vendedor', 'v')
                ->orderBy('n.fecha_creacion', 'DESC')
                ->setMaxResults($limit);

            $recentNegotiations = $qb->getQuery()->getResult();

            foreach ($recentNegotiations as $negotiation) {
                $activities[] = [
                    'type' => 'negotiation_created',
                    'date' => $negotiation->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'description' => 'Nueva negociación por ' . $negotiation->getPrecioPropuesto() . ' créditos',
                    'user' => $negotiation->getComprador()->getNombreUsuario(),
                    'details' => [
                        'negotiation_id' => $negotiation->getId_negociacion(),
                        'buyer' => $negotiation->getComprador()->getNombreUsuario(),
                        'seller' => $negotiation->getVendedor()->getNombreUsuario(),
                        'credits' => $negotiation->getPrecioPropuesto(),
                        'accepted' => $negotiation->isAceptado()
                    ]
                ];
            }

            // Ordenar por fecha
            usort($activities, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Limitar el resultado
            $activities = array_slice($activities, 0, $limit);

            return $this->json([
                'success' => true,
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener actividad reciente: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener la actividad reciente',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}