<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Objeto;
use App\Entity\NegociacionPrecio;
use App\Entity\IntercambioObjeto;
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

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    private NegociacionPrecioRepository $negociacionPrecioRepository;
    private IntercambioObjetoRepository $intercambioObjetoRepository;

    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private ObjetoRepository $objetoRepository,
        private EntityManagerInterface $em,
        NegociacionPrecioRepository $negociacionPrecioRepository,
        IntercambioObjetoRepository $intercambioObjetoRepository
    ) {
        $this->negociacionPrecioRepository = $negociacionPrecioRepository;
        $this->intercambioObjetoRepository = $intercambioObjetoRepository;
    }

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

    #[Route('/user/profile', name: 'user_profile', methods: ['GET'])]
    public function getProfile(): JsonResponse
    {
        try {
            error_log('Iniciando getProfile');
            $request = $this->container->get('request_stack')->getCurrentRequest();
            error_log('Headers: ' . json_encode($request->headers->all()));
            
            $user = $this->getUser();
            error_log('Usuario: ' . ($user ? 'encontrado' : 'no encontrado'));
            
            if (!$user) {
                error_log('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                    'debug' => [
                        'token' => $request->headers->get('Authorization'),
                        'method' => $request->getMethod(),
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }

            error_log('Preparando respuesta de usuario');
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
                    'description' => $user->getDescripcion()
                ]
            ]);
        } catch (\Exception $e) {
            error_log('Error en getProfile: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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

    #[Route('/professionals/search', name: 'professionals_search', methods: ['GET'])]
    public function searchProfessionals(Request $request): JsonResponse
    {
        $query = $request->query->get('query', '');
        
        $professionals = $this->usuarioRepository->findByProfession($query);
        
        $data = array_map(function(Usuario $usuario) {
            return [
                'id' => $usuario->getId_usuario(),
                'name' => $usuario->getNombreUsuario(),
                'profession' => $usuario->getProfesion(),
                'rating' => $usuario->getValoracionPromedio(),
                'reviews_count' => $usuario->getValoraciones()->count(),
                'description' => $usuario->getDescripcion(),
                'photo' => $usuario->getFotoPerfil()
            ];
        }, $professionals);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

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

    #[Route('/users/top-rated', name: 'users_top_rated', methods: ['GET'])]
    public function getTopRatedUsers(): JsonResponse
    {
        try {
            error_log('Iniciando getTopRatedUsers');
            
            $qb = $this->usuarioRepository->createQueryBuilder('u')
                ->orderBy('u.valoracion_promedio', 'DESC')
                ->setMaxResults(10);
            
            $topUsers = $qb->getQuery()->getResult();
            error_log('Usuarios encontrados: ' . count($topUsers));
            
            $usersData = array_map(function($user) {
                return [
                    'id' => $user->getId_usuario(),
                    'username' => $user->getNombreUsuario(),
                    'profession' => $user->getProfesion(),
                    'rating' => $user->getValoracionPromedio(),
                    'sales' => $user->getVentasRealizadas(),
                    'profilePhoto' => $user->getFotoPerfil(),
                    'description' => $user->getDescripcion()
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

    #[Route('/products/top-rated-users', name: 'products_top_rated_users', methods: ['GET'])]
    public function getProductsFromTopRatedUsers(): JsonResponse
    {
        try {
            error_log('Iniciando getProductsFromTopRatedUsers');
            
            // Primero obtenemos los usuarios mejor valorados
            $qb = $this->usuarioRepository->createQueryBuilder('u')
                ->orderBy('u.valoracion_promedio', 'DESC')
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

    #[Route('/products/{id}', name: 'product_update', methods: ['PUT'])]
    public function updateProduct(Request $request, int $id): JsonResponse
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

        if ($product->getUsuario()->getId_usuario() !== $user->getId_usuario()) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este producto'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['title'])) {
            $product->setTitulo($data['title']);
        }
        if (isset($data['description'])) {
            $product->setDescripcion($data['description']);
        }
        if (isset($data['credits'])) {
            $product->setCreditos($data['credits']);
        }

        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Producto actualizado con éxito'
        ]);
    }

    #[Route('/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function deleteProduct(int $id): JsonResponse
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

        if ($product->getUsuario()->getId_usuario() !== $user->getId_usuario()) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este producto'
            ], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($product);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Producto eliminado con éxito'
        ]);
    }

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
                    'id' => $neg->getUsuario()->getId_usuario(),
                    'username' => $neg->getUsuario()->getNombreUsuario(),
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
            // Si ya hay una oferta aceptada, bloquear
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
        $neg->setUsuario($user);
        $neg->setIntercambio($intercambio);
        $neg->setPrecioPropuesto($price);
        $neg->setAceptado(false);
        $neg->setAceptadoVendedor(false);
        $neg->setAceptadoComprador(false);
        $this->em->persist($neg);
        $this->em->flush();
        return $this->json([
            'success' => true,
            'message' => 'Oferta enviada',
            'data' => [
                'id' => $neg->getId_negociacion(),
                'proposedCredits' => $neg->getPrecioPropuesto(),
                'createdAt' => $neg->getFechaCreacion()->format('c'),
            ]
        ]);
    }

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
        }
        if ($isComprador) {
            $neg->setAceptadoComprador(true);
        }
        // Si ambos han aceptado, completar el intercambio
        if ($neg->isAceptadoVendedor() && $neg->isAceptadoComprador()) {
            $neg->setAceptado(true);
            $intercambio->setPrecioPropuesto($neg->getPrecioPropuesto());
            $intercambio->marcarComoCompletado();
            $product->marcarComoIntercambiado();
            // Transferir puntos
            $comprador = $intercambio->getComprador();
            $vendedor = $intercambio->getVendedor();
            $monto = $neg->getPrecioPropuesto();
            if ($comprador->getCreditos() >= $monto) {
                $comprador->setCreditos($comprador->getCreditos() - $monto);
                $vendedor->setCreditos($vendedor->getCreditos() + $monto);
            }
        }
        $this->em->flush();
        return $this->json([
            'success' => true,
            'message' => 'Negociación aceptada'
        ]);
    }

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
}