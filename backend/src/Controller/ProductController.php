<?php

namespace App\Controller;

use App\Entity\Objeto;
use App\Repository\ObjetoRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private ObjetoRepository $objetoRepository,
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->query->get('query', '');
        
        $products = $this->objetoRepository->findBySearchQuery($query);
        
        $data = array_map(function(Objeto $objeto) {
            return [
                'id' => $objeto->getId_objeto(),
                'title' => $objeto->getTitulo(),
                'description' => $objeto->getDescripcion(),
                'credits' => $objeto->getCreditos(),
                'estado' => $objeto->getEstado(),
                'image' => $objeto->getImagen(),
                'seller' => [
                    'id' => $objeto->getUsuario()->getId_usuario(),
                    'name' => $objeto->getUsuario()->getNombreUsuario()
                ],
                'created_at' => $objeto->getFechaCreacion()->format('c')
            ];
        }, $products);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/top-rated-users', name: 'top_rated_users', methods: ['GET'])]
    public function getProductsFromTopRatedUsers(): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            $currentUserId = $currentUser ? $currentUser->getId_usuario() : null;
            
            // Primero obtenemos los usuarios mejor valorados
            $qb = $this->usuarioRepository->createQueryBuilder('u')
                ->where('u.nombre_usuario != :admin')
                ->setParameter('admin', 'ADMIN');
            
            if ($currentUserId) {
                $qb->andWhere('u.id_usuario != :currentUserId')
                   ->setParameter('currentUserId', $currentUserId);
            }
            
            $qb->orderBy('u.valoracion_promedio', 'DESC');
            
            $topUsers = $qb->getQuery()->getResult();

            if (empty($topUsers)) {
                return $this->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Obtenemos los IDs de los usuarios
            $userIds = array_map(function($user) {
                return $user->getId_usuario();
            }, $topUsers);

            // Luego obtenemos los productos de estos usuarios
            $qb = $this->objetoRepository->createQueryBuilder('o')
                ->select('o', 'u')
                ->join('o.usuario', 'u')
                ->where('o.usuario IN (:users)')
                ->andWhere('o.estado = :estado')
                ->setParameter('users', $userIds)
                ->setParameter('estado', Objeto::ESTADO_DISPONIBLE)
                ->orderBy('o.fecha_creacion', 'DESC');

            $products = $qb->getQuery()->getResult();

            $productsData = array_map(function($product) {
                return [
                    'id' => $product->getId_objeto(),
                    'name' => $product->getTitulo(),
                    'description' => $product->getDescripcion(),
                    'price' => $product->getCreditos(),
                    'image' => $product->getImagen(),
                    'user' => [
                        'id' => $product->getUsuario()->getId_usuario(),
                        'username' => $product->getUsuario()->getNombreUsuario(),
                        'rating' => $product->getUsuario()->getValoracionPromedio()
                    ]
                ];
            }, $products);

            // Ordenar por rating del usuario y limitar a 9
            usort($productsData, function($a, $b) {
                return $b['user']['rating'] <=> $a['user']['rating'];
            });

            $productsData = array_slice($productsData, 0, 9);

            return $this->json([
                'success' => true,
                'data' => $productsData
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en getProductsFromTopRatedUsers: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener productos de usuarios mejor valorados',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/my-products', name: 'my_products', methods: ['GET'])]
    public function getMyProducts(): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $products = $this->objetoRepository->findBy(['usuario' => $user]);
            
            $data = array_map(function(Objeto $objeto) {
                return [
                    'id' => (int)$objeto->getId_objeto(),
                    'name' => $objeto->getTitulo(),
                    'description' => $objeto->getDescripcion(),
                    'price' => $objeto->getCreditos(),
                    'state' => (int)$objeto->getEstado(),
                    'image' => $objeto->getImagen(),
                    'created_at' => $objeto->getFechaCreacion()->format('c')
                ];
            }, $products);

            return $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en getMyProducts: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener mis productos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getProduct(string $id): JsonResponse
    {
        try {
            $id = (int)$id;
            $product = $this->objetoRepository->find($id);
            
            if (!$product) {
                return $this->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $response = [
                'success' => true,
                'data' => [
                    'id' => (int)$product->getId_objeto(),
                    'title' => $product->getTitulo(),
                    'description' => $product->getDescripcion(),
                    'credits' => $product->getCreditos(),
                    'image' => $product->getImagen(),
                    'estado' => (int)$product->getEstado(),
                    'seller' => [
                        'id' => (int)$product->getUsuario()->getId_usuario(),
                        'name' => $product->getUsuario()->getNombreUsuario()
                    ],
                    'created_at' => $product->getFechaCreacion()->format('c')
                ]
            ];
            
            return $this->json($response);
        } catch (\Exception $e) {
            $this->logger->error('Error en getProduct: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener el producto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['titulo']) || !isset($data['descripcion']) || !isset($data['creditos'])) {
            return $this->json([
                'success' => false,
                'message' => 'Faltan datos obligatorios'
            ], Response::HTTP_BAD_REQUEST);
        }

        $product = new Objeto();
        $product->setTitulo($data['titulo']);
        $product->setDescripcion($data['descripcion']);
        $product->setCreditos($data['creditos']);
        $product->setUsuario($user);
        $product->setEstado(1); // Estado disponible
        $product->setImagen($data['imagen'] ?? null);

        $this->em->persist($product);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Producto creado con éxito',
            'data' => [
                'id' => $product->getId_objeto(),
                'titulo' => $product->getTitulo(),
                'descripcion' => $product->getDescripcion(),
                'creditos' => $product->getCreditos(),
                'imagen' => $product->getImagen()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function updateProduct(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $product = $this->objetoRepository->find($id);
            if (!$product) {
                return $this->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar que el usuario es el propietario
            if ($product->getUsuario()->getId_usuario() !== $user->getId_usuario()) {
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes permiso para modificar este producto'
                ], Response::HTTP_FORBIDDEN);
            }

            $data = json_decode($request->getContent(), true);
            
            // Validaciones básicas
            if (isset($data['name']) && empty(trim($data['name']))) {
                return $this->json([
                    'success' => false,
                    'message' => 'El nombre del producto no puede estar vacío'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (isset($data['credits']) && (!is_numeric($data['credits']) || $data['credits'] < 1)) {
                return $this->json([
                    'success' => false,
                    'message' => 'El precio debe ser al menos 1 crédito'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Actualizar campos
            if (isset($data['name'])) {
                $product->setTitulo($data['name']);
            }
            if (isset($data['description'])) {
                $product->setDescripcion($data['description']);
            }
            if (isset($data['credits'])) {
                $product->setCreditos((int)$data['credits']);
            }
            if (isset($data['image'])) {
                $product->setImagen($data['image']);
            }
            if (isset($data['state'])) {
                $newState = (int)$data['state'];
                if ($newState >= 1 && $newState <= 3) {
                    $product->setEstado($newState);
                } else {
                    return $this->json([
                        'success' => false,
                        'message' => 'Estado de producto inválido'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Producto actualizado con éxito',
                'data' => [
                    'id' => $product->getId_objeto(),
                    'name' => $product->getTitulo(),
                    'description' => $product->getDescripcion(),
                    'credits' => $product->getCreditos(),
                    'image' => $product->getImagen(),
                    'state' => $product->getEstado(),
                    'seller' => [
                        'id' => $product->getUsuario()->getId_usuario(),
                        'name' => $product->getUsuario()->getNombreUsuario()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al actualizar producto: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}