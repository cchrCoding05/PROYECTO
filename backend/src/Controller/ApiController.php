<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Objeto;
use App\Entity\NegociacionPrecio;
use App\Entity\IntercambioObjeto;
use App\Entity\Valoracion;
use App\Repository\UsuarioRepository;
use App\Repository\ObjetoRepository;
use App\Repository\NegociacionPrecioRepository;
use App\Repository\IntercambioObjetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    private NegociacionPrecioRepository $negociacionPrecioRepository;
    private IntercambioObjetoRepository $intercambioObjetoRepository;
    private LoggerInterface $logger;

    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private ObjetoRepository $objetoRepository,
        private EntityManagerInterface $em,
        NegociacionPrecioRepository $negociacionPrecioRepository,
        IntercambioObjetoRepository $intercambioObjetoRepository,
        LoggerInterface $logger
    ) {
        $this->negociacionPrecioRepository = $negociacionPrecioRepository;
        $this->intercambioObjetoRepository = $intercambioObjetoRepository;
        $this->logger = $logger;
    }
        //Registro de usuario
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Faltan datos obligatorios'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $usuarioExistente = $this->usuarioRepository->findOneBy(['correo' => $data['email']]);
            
            if ($usuarioExistente) {
                return $this->json([
                    'success' => false,
                    'message' => 'Este email ya está registrado'
                ], Response::HTTP_CONFLICT);
            }
            
            $usuario = new Usuario();
            $usuario->setNombreUsuario($data['username']);
            $usuario->setCorreo($data['email']);
            
            // Usar UserPasswordHasherInterface para hashear la contraseña
            $hashedPassword = $passwordHasher->hashPassword($usuario, $data['password']);
            $usuario->setContrasena($hashedPassword);
            
            if (isset($data['descripcion'])) {
                $usuario->setDescripcion($data['descripcion']);
            }
            if (isset($data['profesion'])) {
                $usuario->setProfesion($data['profesion']);
            }
            
            $this->em->persist($usuario);
            $this->em->flush();
            
            return $this->json([
                'success' => true,
                'message' => 'Usuario registrado con éxito',
                'user' => [
                    'id' => $usuario->getId_usuario(),
                    'username' => $usuario->getNombreUsuario(),
                    'email' => $usuario->getCorreo()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Inicio de sesión
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Email y contraseña son requeridos'
                ], Response::HTTP_BAD_REQUEST);
            }

            $usuario = $this->usuarioRepository->findOneBy(['correo' => $data['email']]);
            
            if (!$usuario || !$passwordHasher->isPasswordValid($usuario, $data['password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $token = bin2hex(random_bytes(32));
            $usuario->setToken($token);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $usuario->getId_usuario(),
                    'username' => $usuario->getNombreUsuario(),
                    'email' => $usuario->getCorreo(),
                    'credits' => $usuario->getCreditos(),
                    'profession' => $usuario->getProfesion(),
                    'rating' => $usuario->getValoracionPromedio(),
                    'sales' => $usuario->getVentasRealizadas(),
                    'profilePhoto' => $usuario->getFotoPerfil(),
                    'description' => $usuario->getDescripcion()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error durante el inicio de sesión',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Cierre de sesión
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $user = $this->getUser();
        if ($user) {
            $user->setToken(null);
            $this->em->flush();
        }

        return $this->json([
            'success' => true,
            'message' => 'Sesión cerrada con éxito'
        ]);
    }

    //Obtener perfil de usuario
    #[Route('/user/profile', name: 'user_profile', methods: ['GET'])]
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
            $formattedNegotiations = [];
            
            // Negociaciones como comprador
            foreach ($buyerNegotiations as $negotiation) {
                $formattedNegotiations[] = [
                    'id' => $negotiation->getId_negociacion(),
                    'type' => 'buyer',
                    'professional' => [
                        'id' => $negotiation->getVendedor()->getId_usuario(),
                        'name' => $negotiation->getVendedor()->getNombreUsuario(),
                        'email' => $negotiation->getVendedor()->getCorreo()
                    ],
                    'price' => $negotiation->getPrecioPropuesto(),
                    'status' => [
                        'accepted' => $negotiation->isAceptado()
                    ],
                    'createdAt' => $negotiation->getFechaCreacion()->format('Y-m-d H:i:s')
                ];
            }

            // Negociaciones como vendedor
            foreach ($sellerNegotiations as $negotiation) {
                $formattedNegotiations[] = [
                    'id' => $negotiation->getId_negociacion(),
                    'type' => 'seller',
                    'professional' => [
                        'id' => $negotiation->getComprador()->getId_usuario(),
                        'name' => $negotiation->getComprador()->getNombreUsuario(),
                        'email' => $negotiation->getComprador()->getCorreo()
                    ],
                    'price' => $negotiation->getPrecioPropuesto(),
                    'status' => [
                        'accepted' => $negotiation->isAceptado()
                    ],
                    'createdAt' => $negotiation->getFechaCreacion()->format('Y-m-d H:i:s')
                ];
            }

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

    //Actualizar perfil de usuario
    #[Route('/user/profile', name: 'user_profile_update', methods: ['PUT'])]
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

    //Buscar profesionales
    #[Route('/professionals/search', name: 'search_professionals', methods: ['GET'])]
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

    //Obtener profesional
    #[Route('/professionals/{id}', name: 'professional_get', methods: ['GET'])]
    public function getProfessional(int $id): JsonResponse
    {
        $professional = $this->usuarioRepository->find($id);
        
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
    }

    //Obtener valoraciones de profesional
    #[Route('/professionals/{id}/ratings', name: 'professional_ratings', methods: ['GET'])]
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

    //Buscar productos
    #[Route('/products/search', name: 'products_search', methods: ['GET'])]
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

    //Obtener usuarios mejor valorados
    #[Route('/users/top-rated', name: 'users_top_rated', methods: ['GET'])]
    public function getTopRatedUsers(): JsonResponse
    {
        try {
            error_log('Iniciando getTopRatedUsers');
            
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
            error_log('Usuarios encontrados: ' . count($topUsers));
            
            $usersData = array_map(function($user) {
                // Obtener valoraciones del profesional
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
            error_log('Error en getTopRatedUsers: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener usuarios mejor valorados',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Obtener productos de usuarios mejor valorados
    #[Route('/products/top-rated-users', name: 'products_top_rated_users', methods: ['GET'])]
    public function getProductsFromTopRatedUsers(): JsonResponse
    {
        try {
            error_log('Iniciando getProductsFromTopRatedUsers');
            
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
            
            $qb->orderBy('u.valoracion_promedio', 'DESC')
               ->setMaxResults(5);
            
            error_log('Query usuarios: ' . $qb->getQuery()->getSQL());
            $topUsers = $qb->getQuery()->getResult();
            error_log('Usuarios encontrados: ' . count($topUsers));

            if (empty($topUsers)) {
                error_log('No se encontraron usuarios');
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
                ->select('o', 'u')  // Seleccionamos explícitamente el usuario
                ->join('o.usuario', 'u')  // Hacemos join con la tabla de usuarios
                ->where('o.usuario IN (:users)')
                ->andWhere('o.estado = :estado')
                ->setParameter('users', $userIds)
                ->setParameter('estado', Objeto::ESTADO_DISPONIBLE)
                ->orderBy('o.fecha_creacion', 'DESC')
                ->setMaxResults(10);
            
            error_log('Query productos: ' . $qb->getQuery()->getSQL());
            error_log('Parámetros: ' . json_encode([
                'users' => $userIds,
                'estado' => Objeto::ESTADO_DISPONIBLE
            ]));
            
            $products = $qb->getQuery()->getResult();
            error_log('Productos encontrados: ' . count($products));

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

            error_log('Datos procesados correctamente');
            return $this->json([
                'success' => true,
                'data' => $productsData
            ]);
        } catch (\Exception $e) {
            error_log('Error en getProductsFromTopRatedUsers: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener productos de usuarios mejor valorados',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Obtener productos de usuario
    #[Route('/products/my-products', name: 'products_my_products', methods: ['GET'])]
    public function getMyProducts(): JsonResponse
    {
        try {
            error_log('Iniciando getMyProducts');
            
            $user = $this->getUser();
            error_log('Usuario obtenido: ' . ($user ? 'Sí' : 'No'));
            
            if (!$user) {
                error_log('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            error_log('Buscando productos para usuario ID: ' . $user->getId_usuario());
            $products = $this->objetoRepository->findBy(['usuario' => $user]);
            error_log('Productos encontrados: ' . count($products));
            
            $data = array_map(function(Objeto $objeto) {
                error_log('Procesando objeto ID: ' . $objeto->getId_objeto());
                $id = (int)$objeto->getId_objeto();
                $estado = (int)$objeto->getEstado();
                error_log("ID convertido: $id, Estado convertido: $estado");
                
                return [
                    'id' => $id,
                    'name' => $objeto->getTitulo(),
                    'description' => $objeto->getDescripcion(),
                    'price' => $objeto->getCreditos(),
                    'state' => $estado,
                    'image' => $objeto->getImagen(),
                    'created_at' => $objeto->getFechaCreacion()->format('c')
                ];
            }, $products);

            error_log('Datos procesados: ' . json_encode($data));
            return $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            error_log('Error en getMyProducts: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('Línea del error: ' . $e->getLine());
            error_log('Archivo del error: ' . $e->getFile());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener mis productos',
                'error' => $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Obtener producto
    #[Route('/products/{id}', name: 'product_get', methods: ['GET'])]
    public function getProduct(string $id): JsonResponse
    {
        try {
            error_log('Iniciando getProduct con ID: ' . $id);
            error_log('Tipo de ID: ' . gettype($id));
            
            $id = (int)$id;
            error_log('ID convertido a entero: ' . $id);
            
            $product = $this->objetoRepository->find($id);
            error_log('Producto encontrado: ' . ($product ? 'Sí' : 'No'));
            
            if (!$product) {
                error_log('Producto no encontrado con ID: ' . $id);
                return $this->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            error_log('Producto encontrado: ' . $product->getId_objeto());
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
            error_log('Respuesta preparada: ' . json_encode($response));
            
            return $this->json($response);
        } catch (\Exception $e) {
            error_log('Error en getProduct: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('Línea del error: ' . $e->getLine());
            error_log('Archivo del error: ' . $e->getFile());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener el producto',
                'error' => $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Crear producto
    #[Route('/products', name: 'product_create', methods: ['POST'])]
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
        $product->setEstado(1); // Estado por defecto: disponible
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

    //Actualizar producto
    #[Route('/products/{id}', name: 'product_update', methods: ['PUT'])]
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
                // Validar que el estado es válido (1, 2 o 3)
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
            error_log('Error al actualizar producto: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Eliminar producto (admin)
    #[Route('/admin/products/{id}', name: 'admin_delete_product', methods: ['DELETE'])]
    public function deleteProductAdmin(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || $user->getNombreUsuario() !== 'ADMIN') {
            return $this->json([
                'success' => false,
                'message' => 'Acceso no autorizado'
            ], Response::HTTP_FORBIDDEN);
        }

        $product = $this->objetoRepository->find($id);
        if (!$product) {
            return $this->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($product);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Producto eliminado con éxito'
        ]);
    }

    //Obtener saldo de créditos
    #[Route('/credits/balance', name: 'credits_balance', methods: ['GET'])]
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

    //Obtener historial de créditos
    #[Route('/credits/history', name: 'credits_history', methods: ['GET'])]
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

    //Transferencia de créditos
    #[Route('/credits/transfer', name: 'credits_transfer', methods: ['POST'])]
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

    //Obtener negociaciones de producto
    #[Route('/products/{id}/negotiations', name: 'product_negotiations', methods: ['GET'])]
    public function getNegotiations(int $id): JsonResponse
    {
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
        $intercambio = $this->intercambioObjetoRepository->findOneBy(['objeto' => $product]);
        if (!$intercambio) {
            return $this->json([
                'success' => true,
                'data' => []
            ]);
        }
        $negociaciones = $this->negociacionPrecioRepository->findBy(['intercambio' => $intercambio], ['fecha_creacion' => 'ASC']);
        $data = array_map(function($neg) {
            return [
                'id' => $neg->getId_negociacion(),
                'user' => [
                    'id' => $neg->getComprador()->getId_usuario(),
                    'username' => $neg->getComprador()->getNombreUsuario(),
                ],
                'proposedCredits' => $neg->getPrecioPropuesto(),
                'accepted' => $neg->isAceptado(),
                'createdAt' => $neg->getFechaCreacion()->format('c'),
            ];
        }, $negociaciones);
        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    //Proponer precio
    #[Route('/products/{id}/propose-price', name: 'product_propose_price', methods: ['POST'])]
    public function proposePrice(Request $request, int $id): JsonResponse
    {
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

        // Solo validar que el producto no esté intercambiado
        if ($product->getEstado() === Objeto::ESTADO_INTERCAMBIADO) {
            return $this->json([
                'success' => false,
                'message' => 'Este producto ya ha sido intercambiado'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 1) {
            return $this->json([
                'success' => false,
                'message' => 'Precio inválido'
            ], Response::HTTP_BAD_REQUEST);
        }
        $price = (int)$data['price'];

        // Validar saldo si es comprador
        if ($user->getId_usuario() !== $product->getUsuario()->getId_usuario() && $user->getCreditos() < $price) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes suficientes puntos para ofertar'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar o crear intercambio
        $intercambio = $this->intercambioObjetoRepository->findOneBy(['objeto' => $product]);
        if (!$intercambio) {
            $intercambio = new IntercambioObjeto();
            $intercambio->setObjeto($product);
            $intercambio->setVendedor($product->getUsuario());
            $intercambio->setComprador($user);
            $intercambio->setPrecioPropuesto($price);
            $this->em->persist($intercambio);
        } else {
            // Solo validar si ya hay una oferta aceptada
            foreach ($intercambio->getNegociaciones() as $neg) {
                if ($neg->isAceptado()) {
                    return $this->json([
                        'success' => false,
                        'message' => 'La negociación ya ha sido aceptada'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Cambiar estado a reservado si está disponible
        if ($product->getEstado() === Objeto::ESTADO_DISPONIBLE) {
            $product->setEstado(Objeto::ESTADO_RESERVADO);
        }

        // Crear nueva negociación
        $neg = new NegociacionPrecio();
        $neg->setComprador($user);
        $neg->setVendedor($product->getUsuario());
        $neg->setPrecioPropuesto($price);
        $neg->setAceptado(false);
        $neg->setAceptadoVendedor(false);
        $neg->setAceptadoComprador(false);
        
        // Agregar la negociación al intercambio usando el método addNegociacion
        $intercambio->addNegociacion($neg);
        
        $this->em->persist($neg);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Oferta enviada',
            'data' => [
                'id' => $neg->getId_negociacion(),
                'proposedCredits' => $neg->getPrecioPropuesto(),
                'createdAt' => $neg->getFechaCreacion()->format('c'),
                'isActive' => $neg->isAceptado()
            ]
        ]);
    }

    //Aceptar negociación
    #[Route('/products/{productId}/negotiations/{negotiationId}/accept', name: 'negotiation_accept', methods: ['POST'])]
    public function acceptNegotiation(int $productId, int $negotiationId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $neg = $this->negociacionPrecioRepository->find($negotiationId);
        if (!$neg) {
            return $this->json([
                'success' => false,
                'message' => 'Negociación no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }
        $intercambio = $neg->getIntercambio();
        $product = $intercambio->getObjeto();
        if ($product->getId_objeto() !== $productId) {
            return $this->json([
                'success' => false,
                'message' => 'Producto incorrecto'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Solo vendedor o comprador pueden aceptar
        $isVendedor = $user->getId_usuario() === $intercambio->getVendedor()->getId_usuario();
        $isComprador = $user->getId_usuario() === $intercambio->getComprador()->getId_usuario();
        if (!$isVendedor && !$isComprador) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes permiso para aceptar esta negociación'
            ], Response::HTTP_FORBIDDEN);
        }

        // Si ya hay una aceptada, bloquear
        foreach ($intercambio->getNegociaciones() as $n) {
            if ($n->isAceptado()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ya hay una negociación aceptada'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Registrar aceptación
        if ($isVendedor) {
            $neg->setAceptadoVendedor(true);
            // Si el vendedor acepta, cambiar estado del producto a intercambiado
            $product->setEstado(Objeto::ESTADO_INTERCAMBIADO);
        }
        if ($isComprador) {
            $neg->setAceptadoComprador(true);
        }

        // Si ambos han aceptado, completar el intercambio
        if ($neg->isAceptadoVendedor() && $neg->isAceptadoComprador()) {
            $neg->setAceptado(true);
            $intercambio->setPrecioPropuesto($neg->getPrecioPropuesto());
            $intercambio->marcarComoCompletado();
            
            // Transferir puntos
            $comprador = $intercambio->getComprador();
            $vendedor = $intercambio->getVendedor();
            $monto = $neg->getPrecioPropuesto();
            
            if ($comprador->getCreditos() >= $monto) {
                $comprador->setCreditos($comprador->getCreditos() - $monto);
                $vendedor->setCreditos($vendedor->getCreditos() + $monto);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'El comprador no tiene suficientes créditos'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->em->flush();
        return $this->json([
            'success' => true,
            'message' => 'Negociación aceptada'
        ]);
    }

    //Rechazar negociación
    #[Route('/products/{productId}/negotiations/{negotiationId}/reject', name: 'negotiation_reject', methods: ['POST'])]
    public function rejectNegotiation(int $productId, int $negotiationId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $neg = $this->negociacionPrecioRepository->find($negotiationId);
        if (!$neg) {
            return $this->json([
                'success' => false,
                'message' => 'Negociación no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }
        $intercambio = $neg->getIntercambio();
        $product = $intercambio->getObjeto();
        if ($product->getId_objeto() !== $productId) {
            return $this->json([
                'success' => false,
                'message' => 'Producto incorrecto'
            ], Response::HTTP_BAD_REQUEST);
        }
        // Solo vendedor o comprador pueden rechazar
        $isVendedor = $user->getId_usuario() === $intercambio->getVendedor()->getId_usuario();
        $isComprador = $user->getId_usuario() === $intercambio->getComprador()->getId_usuario();
        if (!$isVendedor && !$isComprador) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes permiso para rechazar esta negociación'
            ], Response::HTTP_FORBIDDEN);
        }
        // Si ya hay una aceptada, bloquear
        foreach ($intercambio->getNegociaciones() as $n) {
            if ($n->isAceptado()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ya hay una negociación aceptada'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        // Eliminar la negociación rechazada
        $this->em->remove($neg);
        $this->em->flush();
        // Si no quedan negociaciones pendientes, volver a disponible
        $negociacionesRestantes = $this->negociacionPrecioRepository->findBy(['intercambio' => $intercambio]);
        if (count($negociacionesRestantes) === 0) {
            $product->setEstado(Objeto::ESTADO_DISPONIBLE);
            $this->em->flush();
        }
        return $this->json([
            'success' => true,
            'message' => 'Negociación rechazada'
        ]);
    }

    //Obtener negociaciones del usuario
    #[Route('/negotiations/my-negotiations', name: 'get_my_negotiations', methods: ['GET'])]
    public function getMyNegotiations(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Obtener todas las negociaciones donde el usuario es comprador o vendedor
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from('App\Entity\NegociacionPrecio', 'n')
               ->where('n.comprador = :userId OR n.vendedor = :userId')
               ->setParameter('userId', $user->getId_usuario())
               ->orderBy('n.fecha_creacion', 'DESC');

            $negotiations = $qb->getQuery()->getResult();

            $formattedNegotiations = [];
            foreach ($negotiations as $negotiation) {
                $intercambio = $negotiation->getIntercambio();
                $objeto = $intercambio ? $intercambio->getObjeto() : null;
                
                // Debug de los estados
                $this->logger->info('Estado de negociación', [
                    'id' => $negotiation->getId_negociacion(),
                    'aceptado' => $negotiation->isAceptado(),
                    'aceptado_vendedor' => $negotiation->isAceptadoVendedor(),
                    'aceptado_comprador' => $negotiation->isAceptadoComprador(),
                    'estado_objeto' => $objeto ? $objeto->getEstado() : null
                ]);

                // Determinar el estado de la negociación
                $status = 1; // Por defecto: activa
                
                // Si el objeto está reservado, la negociación está activa
                if ($objeto && $objeto->getEstado() === Objeto::ESTADO_RESERVADO) {
                    $status = 1; // Activa
                }
                // Si el vendedor ha aceptado, la negociación está finalizada
                else if ($negotiation->isAceptadoVendedor()) {
                    $status = 2; // Finalizada
                }
                // Si el comprador o vendedor han rechazado, la negociación está finalizada
                else if (!$negotiation->isAceptadoVendedor() && !$negotiation->isAceptadoComprador()) {
                    $status = 3; // Finalizada (rechazada)
                }

                // Determinar si está activa
                $isActive = $status === 1;
                
                $this->logger->info('Estado final', [
                    'id' => $negotiation->getId_negociacion(),
                    'status' => $status,
                    'isActive' => $isActive
                ]);
                
                $formattedNegotiations[] = [
                    'id' => $negotiation->getId_negociacion(),
                    'product' => [
                        'id' => $objeto ? $objeto->getId_objeto() : null,
                        'name' => $objeto ? $objeto->getTitulo() : 'Producto no disponible',
                        'image' => $objeto ? $objeto->getImagen() : null,
                        'credits' => $objeto ? $objeto->getCreditos() : 0,
                        'state' => $objeto ? $objeto->getEstado() : null
                    ],
                    'seller' => [
                        'id' => $negotiation->getVendedor()->getId_usuario(),
                        'name' => $negotiation->getVendedor()->getNombreUsuario()
                    ],
                    'buyer' => [
                        'id' => $negotiation->getComprador()->getId_usuario(),
                        'name' => $negotiation->getComprador()->getNombreUsuario()
                    ],
                    'proposedCredits' => $negotiation->getPrecioPropuesto(),
                    'status' => $status,
                    'date' => $negotiation->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'isSeller' => $negotiation->getVendedor()->getId_usuario() === $user->getId_usuario(),
                    'isActive' => $isActive
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $formattedNegotiations
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener negociaciones: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener las negociaciones',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Listar usuarios (admin)
    #[Route('/admin/users', name: 'admin_list_users', methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || $user->getNombreUsuario() !== 'ADMIN') {
            return $this->json([
                'success' => false,
                'message' => 'Acceso no autorizado'
            ], Response::HTTP_FORBIDDEN);
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
                'foto_perfil' => $user->getFotoPerfil()
            ];
        }, $users);

        return $this->json([
            'success' => true,
            'data' => $formattedUsers
        ]);
    }

    // Eliminar usuario (admin)
    #[Route('/admin/users/{id}', name: 'admin_delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
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

    // Listar productos (admin)
    #[Route('/admin/products', name: 'admin_list_products', methods: ['GET'])]
    public function listProducts(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || $user->getNombreUsuario() !== 'ADMIN') {
            return $this->json([
                'success' => false,
                'message' => 'Acceso no autorizado'
            ], Response::HTTP_FORBIDDEN);
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
                'credits' => $product->getCreditos(),
                'state' => $product->getEstado(),
                'image' => $product->getImagen(),
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

    //Obtener chat con profesional
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

    //Valorar profesional
    #[Route('/professionals/{id}/rate', name: 'professional_rate', methods: ['POST'])]
    public function rateProfessional(Request $request, int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando rateProfessional', ['id' => $id]);
            
            $user = $this->getUser();
            if (!$user) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si el usuario está intentando valorarse a sí mismo
            if ($user->getId_usuario() === $profesional->getId_usuario()) {
                return $this->json([
                    'success' => false,
                    'message' => 'No puedes valorarte a ti mismo'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verificar si ya existe una valoración del usuario al profesional
            $valoracionExistente = $this->em->getRepository(Valoracion::class)->findOneBy([
                'usuario' => $user,
                'profesional' => $profesional
            ]);

            if ($valoracionExistente) {
                $this->logger->warning('Ya existe una valoración del usuario al profesional');
                return $this->json([
                    'success' => false,
                    'message' => 'Ya has valorado a este profesional'
                ], Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($request->getContent(), true);
            $this->logger->info('Datos recibidos', ['data' => $data]);

            if (!isset($data['puntuacion']) || !isset($data['comentario'])) {
                $this->logger->warning('Datos incompletos', ['data' => $data]);
                return $this->json([
                    'success' => false,
                    'message' => 'Se requiere puntuación y comentario'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validar que la puntuación esté entre 1 y 5
            if (!is_numeric($data['puntuacion']) || $data['puntuacion'] < 1 || $data['puntuacion'] > 5) {
                $this->logger->warning('Puntuación inválida', ['puntuacion' => $data['puntuacion']]);
                return $this->json([
                    'success' => false,
                    'message' => 'La puntuación debe estar entre 1 y 5'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validar que el comentario no esté vacío
            if (empty(trim($data['comentario']))) {
                $this->logger->warning('Comentario vacío');
                return $this->json([
                    'success' => false,
                    'message' => 'El comentario no puede estar vacío'
                ], Response::HTTP_BAD_REQUEST);
            }

            $valoracion = new Valoracion();
            $valoracion->setUsuario($user);
            $valoracion->setProfesional($profesional);
            $valoracion->setPuntuacion((int)$data['puntuacion']);
            $valoracion->setComentario(trim($data['comentario']));
            $valoracion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($valoracion);
            $this->em->flush();

            // Actualizar la valoración promedio del profesional
            $profesional->actualizarValoracionPromedio();
            $this->em->flush();

            $this->logger->info('Valoración creada exitosamente', [
                'id' => $valoracion->getId_valoracion(),
                'usuario' => $user->getId_usuario(),
                'profesional' => $profesional->getId_usuario()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Valoración creada exitosamente',
                'data' => [
                    'id' => $valoracion->getId_valoracion(),
                    'puntuacion' => $valoracion->getPuntuacion(),
                    'comentario' => $valoracion->getComentario(),
                    'fecha' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s')
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

    //Iniciar chat con profesional
    #[Route('/professional-chat/{id}/start', name: 'professional_chat_start', methods: ['POST'])]
    public function startProfessionalChat(int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando startProfessionalChat', ['id' => $id]);
            
            if (!$this->getUser()) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json(['error' => 'Usuario no autenticado'], 401);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return $this->json(['error' => 'Profesional no encontrado'], 404);
            }

            // Verificar si ya existe una negociación activa
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from(NegociacionPrecio::class, 'n')
               ->where('n.comprador = :comprador')
               ->andWhere('n.vendedor = :vendedor')
               ->andWhere('n.aceptado = false')
               ->setParameter('comprador', $this->getUser())
               ->setParameter('vendedor', $profesional);

            $negociacionExistente = $qb->getQuery()->getOneOrNullResult();

            if ($negociacionExistente) {
                $this->logger->info('Ya existe una negociación activa', [
                    'id' => $negociacionExistente->getId_negociacion()
                ]);
                return $this->json([
                    'message' => 'Ya existe una negociación activa',
                    'negociacion_id' => $negociacionExistente->getId_negociacion()
                ]);
            }

            // Crear nueva negociación directa entre usuario y profesional
            $negociacion = new NegociacionPrecio();
            $negociacion->setComprador($this->getUser());
            $negociacion->setVendedor($profesional);
            $negociacion->setPrecioPropuesto(0);
            $negociacion->setAceptado(false);
            $negociacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($negociacion);
            $this->em->flush();

            $this->logger->info('Negociación creada exitosamente', [
                'id' => $negociacion->getId_negociacion(),
                'comprador' => $this->getUser()->getId_usuario(),
                'vendedor' => $profesional->getId_usuario()
            ]);

            return $this->json([
                'message' => 'Chat iniciado exitosamente',
                'negociacion_id' => $negociacion->getId_negociacion()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al iniciar chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => 'Error al iniciar el chat'], 500);
        }
    }

    #[Route('/professionals', name: 'get_professionals', methods: ['GET'])]
    public function getProfessionals(Request $request): JsonResponse
    {
        try {
            $this->logger->info('Iniciando getProfessionals');
            
            $professionals = $this->usuarioRepository->findBy(['tipo_usuario' => 'profesional']);
            $result = [];

            foreach ($professionals as $professional) {
                // Obtener todas las valoraciones del profesional
                $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $professional]);
                $valoracionesArray = [];
                
                foreach ($valoraciones as $valoracion) {
                    $valoracionesArray[] = [
                        'id' => $valoracion->getId_valoracion(),
                        'puntuacion' => $valoracion->getPuntuacion(),
                        'comentario' => $valoracion->getComentario(),
                        'fecha' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s'),
                        'usuario' => [
                            'id' => $valoracion->getUsuario()->getId_usuario(),
                            'nombre' => $valoracion->getUsuario()->getNombre(),
                            'apellidos' => $valoracion->getUsuario()->getApellidos()
                        ]
                    ];
                }

                $result[] = [
                    'id' => $professional->getId_usuario(),
                    'nombre' => $professional->getNombre(),
                    'apellidos' => $professional->getApellidos(),
                    'email' => $professional->getEmail(),
                    'telefono' => $professional->getTelefono(),
                    'descripcion' => $professional->getDescripcion(),
                    'valoracion_promedio' => $professional->getValoracionPromedio(),
                    'valoraciones' => $valoracionesArray,
                    'servicios' => $this->getServiciosArray($professional),
                    'objetos' => $this->getObjetosArray($professional)
                ];
            }

            $this->logger->info('Profesionales obtenidos exitosamente', ['count' => count($result)]);
            return $this->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener profesionales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener profesionales',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}