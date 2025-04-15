<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
// use Symfony\Component\Security\Http\Attribute\IsGranted; // Comentado temporalmente
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Comentado temporalmente
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Usuario;
use App\Entity\ImagenObjeto;
use App\Repository\UsuarioRepository;
use App\Repository\ObjetoRepository;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    private $em;
    // private $passwordHasher; // Comentado temporalmente
    
    public function __construct(EntityManagerInterface $em/*, UserPasswordHasherInterface $passwordHasher*/)
    {
        $this->em = $em;
        // $this->passwordHasher = $passwordHasher; // Comentado temporalmente
    }
    
    private function createCorsResponse($data = null, $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        // Configurar CORS una sola vez
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '3600');
        
        return $response;
    }
    
    #[Route('/options', name: 'options', methods: ['OPTIONS'])]
    #[Route('/{any}', name: 'any_options', requirements: ['any' => '.+'], methods: ['OPTIONS'])]
    public function handleOptions(): Response
    {
        return $this->createCorsResponse(null);
    }
    
    // Método de registro simplificado y sin seguridad (temporalmente)
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $username = $data['username'] ?? null;
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
            
            if (!$username || !$email || !$password) {
                return $this->createCorsResponse([
                    'success' => false,
                    'message' => 'Faltan datos obligatorios'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Comprobar si el usuario ya existe
            $usuarioExistente = $usuarioRepository->findOneBy(['correo' => $email]);
            
            if ($usuarioExistente) {
                return $this->createCorsResponse([
                    'success' => false,
                    'message' => 'Este email ya está registrado'
                ], Response::HTTP_CONFLICT);
            }
            
            // Crear nuevo usuario
            $usuario = new Usuario();
            $usuario->setNombreUsuario($username);
            $usuario->setCorreo($email);
            // Ciframos la contraseña con password_hash
            $usuario->setContrasena(password_hash($password, PASSWORD_DEFAULT));
            
            // Opcional, si proporcionan otros datos
            if (isset($data['descripcion'])) {
                $usuario->setDescripcion($data['descripcion']);
            }
            if (isset($data['profesion'])) {
                $usuario->setProfesion($data['profesion']);
            }
            
            // Guardar en la base de datos
            $entityManager->persist($usuario);
            $entityManager->flush();
            
            return $this->createCorsResponse([
                'success' => true,
                'message' => 'Usuario registrado con éxito',
                'user' => [
                    'id' => $usuario->getId_usuario(),
                    'username' => $usuario->getNombreUsuario(),
                    'email' => $usuario->getCorreo()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // Método de login simplificado y sin seguridad (temporalmente)
    #[Route('/login_check', name: 'login_check', methods: ['POST'])]
    public function login(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
            
            if (!$email || !$password) {
                return $this->createCorsResponse([
                    'success' => false,
                    'message' => 'Faltan datos de login'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $usuario = $usuarioRepository->findOneBy(['correo' => $email]);
            
            if (!$usuario) {
                return $this->createCorsResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Verificar la contraseña utilizando password_verify
            if (!password_verify($password, $usuario->getContrasena())) {
                return $this->createCorsResponse([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            return $this->createCorsResponse([
                'success' => true,
                'token' => 'token_' . $usuario->getId_usuario(),
                'user' => [
                    'id' => $usuario->getId_usuario(),
                    'username' => $usuario->getNombreUsuario(),
                    'email' => $usuario->getCorreo(),
                    'credits' => $usuario->getCreditos()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Error de autenticación',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/user/current', name: 'current_user', methods: ['GET'])]
    public function getCurrentUser(Request $request): JsonResponse
    {
        // En una aplicación real, extraeríamos el usuario del token
        // Para pruebas, devolvemos un usuario demo
        return $this->createCorsResponse([
            'id' => 1,
            'username' => 'usuario_demo',
            'email' => 'demo@example.com',
            'credits' => 500,
            'description' => 'Usuario de prueba para desarrollo',
            'profession' => 'Desarrollador',
            'avatarUrl' => null
        ]);
    }
    
    // Método simple para el cierre de sesión
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // En una aplicación real, invalidaríamos el token
        // Aquí simplemente confirmamos la operación
        return $this->createCorsResponse([
            'success' => true,
            'message' => 'Sesión cerrada con éxito'
        ]);
    }
    
    // Método de actualización de perfil simplificado (temporalmente)
    #[Route('/user/profile', name: 'update_profile', methods: ['PUT'])]
    // #[IsGranted('ROLE_USER')] // Comentado temporalmente
    public function updateProfile(Request $request): JsonResponse
    {
        // Mensaje temporal
        return $this->createCorsResponse([
            'success' => false,
            'message' => 'La actualización de perfil está temporalmente deshabilitada debido a problemas con las dependencias de seguridad.'
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
    
    #[Route('/professionals/search', name: 'search_professionals', methods: ['GET'])]
    public function searchProfessionals(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        try {
            // Obtener el parámetro de búsqueda
            $query = $request->query->get('q', '');
            
            // Registrar la consulta para depuración
            $this->logDebug("Buscando profesionales con query: " . $query);
            
            // Obtener todos los usuarios (en un caso real, filtraríamos por profesionales)
            $usuarios = $usuarioRepository->findAll();
            
            // Transformar los datos para la respuesta
            $result = [];
            foreach ($usuarios as $usuario) {
                // Solo incluimos usuarios con profesión definida
                if ($usuario->getProfesion()) {
                    $profesional = [
                        'id' => $usuario->getId_usuario(),
                        'name' => $usuario->getNombreUsuario(),
                        'profession' => $usuario->getProfesion(),
                        'rating' => 4, // Ejemplo estático, en un caso real calcularíamos esto
                        'ratingCount' => 10, // Ejemplo estático
                        'description' => $usuario->getDescripcion(),
                        'avatarUrl' => $usuario->getFotoPerfil(),
                        'email' => $usuario->getCorreo()
                    ];
                    $result[] = $profesional;
                    
                    // Registrar los profesionales encontrados
                    $this->logDebug("Profesional encontrado: " . json_encode([
                        'name' => $profesional['name'],
                        'profession' => $profesional['profession']
                    ]));
                }
            }
            
            // Filtrar por consulta si se proporciona
            if (!empty($query)) {
                $filteredResult = [];
                foreach ($result as $professional) {
                    // Convertimos todo a minúsculas para una comparación sin distinción de mayúsculas/minúsculas
                    $nameMatch = stripos($professional['name'], $query) !== false;
                    $professionMatch = stripos($professional['profession'], $query) !== false;
                    $descriptionMatch = stripos($professional['description'], $query) !== false;
                    
                    if ($nameMatch || $professionMatch || $descriptionMatch) {
                        $filteredResult[] = $professional;
                        
                        // Registrar qué campo coincidió con la búsqueda
                        $matchReason = [];
                        if ($nameMatch) $matchReason[] = "nombre";
                        if ($professionMatch) $matchReason[] = "profesión";
                        if ($descriptionMatch) $matchReason[] = "descripción";
                        
                        $this->logDebug("Coincidencia encontrada para '" . $query . "' en " . 
                                     implode(", ", $matchReason) . ": " . $professional['name']);
                    }
                }
                $result = $filteredResult;
            }
            
            // Registrar número de resultados
            $this->logDebug("Total resultados encontrados: " . count($result));
            
            // Devolver un array vacío si no hay resultados
            return $this->createCorsResponse(array_values($result));
        } catch (\Exception $e) {
            // Si hay un error, devolver un mensaje descriptivo
            $this->logDebug("Error en búsqueda de profesionales: " . $e->getMessage());
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Error al cargar datos desde la base de datos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Método auxiliar para registrar información de depuración
     */
    private function logDebug($message): void
    {
        // En producción, esto debería usar un logger adecuado
        file_put_contents(
            __DIR__ . '/../../var/log/debug.log', 
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 
            FILE_APPEND
        );
    }
    
    #[Route('/products/search', name: 'search_products', methods: ['GET'])]
    public function searchProducts(Request $request, ObjetoRepository $objetoRepository): JsonResponse
    {
        try {
            // Obtener el parámetro de búsqueda
            $query = $request->query->get('q', '');
            
            // Obtener todos los objetos
            $objetos = $objetoRepository->findAll();
            
            // Transformar los datos para la respuesta
            $result = [];
            foreach ($objetos as $objeto) {
                $usuario = $objeto->getUsuario();
                if ($usuario) {
                    // Obtener la primera imagen si existe
                    $imagenUrl = 'https://via.placeholder.com/150'; // Imagen por defecto
                    if ($objeto->getImagenes()->count() > 0) {
                        $imagen = $objeto->getImagenes()->first();
                        $imagenUrl = $imagen->getUrlImagen();
                    }
                    
                    $result[] = [
                        'id' => $objeto->getId_objeto(),
                        'name' => $objeto->getTitulo(),
                        'description' => $objeto->getDescripcion(),
                        'price' => $objeto->getCreditos(),
                        'imageUrl' => $imagenUrl,
                        'seller' => [
                            'id' => $usuario->getId_usuario(),
                            'username' => $usuario->getNombreUsuario(),
                            'sales' => 0 // Ejemplo estático, en un caso real calcularíamos esto
                        ]
                    ];
                }
            }
            
            // Filtrar por consulta si se proporciona
            if (!empty($query)) {
                $result = array_filter($result, function($product) use ($query) {
                    return stripos($product['name'], $query) !== false || 
                           stripos($product['description'], $query) !== false;
                });
            }
            
            // Devolver un array vacío si no hay resultados
            return $this->createCorsResponse(array_values($result));
        } catch (\Exception $e) {
            // Si hay un error, devolver un mensaje descriptivo
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Error al cargar datos desde la base de datos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/products/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(int $id, ObjetoRepository $objetoRepository): JsonResponse
    {
        try {
            // Buscar el objeto por su ID
            $objeto = $objetoRepository->find($id);
            
            if ($objeto) {
                $usuario = $objeto->getUsuario();
                
                // Obtener la primera imagen si existe
                $imagenUrl = 'https://via.placeholder.com/300x200'; // Imagen por defecto
                if ($objeto->getImagenes()->count() > 0) {
                    $imagen = $objeto->getImagenes()->first();
                    $imagenUrl = $imagen->getUrl();
                }
                
                $result = [
                    'id' => $objeto->getId_objeto(),
                    'name' => $objeto->getTitulo(),
                    'description' => $objeto->getDescripcion(),
                    'price' => $objeto->getCreditos(),
                    'imageUrl' => $imagenUrl,
                    'seller' => [
                        'id' => $usuario->getId_usuario(),
                        'username' => $usuario->getNombreUsuario(),
                        'sales' => 0 // Ejemplo estático, en un caso real calcularíamos esto
                    ]
                ];
                
                return $this->createCorsResponse($result);
            } else {
                // Si no se encuentra el producto, devolver un error
                return $this->createCorsResponse([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            // Si hay un error, devolver un mensaje descriptivo
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Error al cargar datos desde la base de datos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/credits/balance', name: 'get_balance', methods: ['GET'])]
    // #[IsGranted('ROLE_USER')] // Comentado temporalmente
    public function getBalance(): JsonResponse
    {
        // Simulación: en una app real, extraerías el usuario del token
        // Para fines de demostración, simplemente devolvemos un usuario de prueba
        return $this->createCorsResponse([
            'credits' => 500
        ]);
    }
    
    #[Route('/products/{id}/propose-price', name: 'propose_price', methods: ['POST'])]
    // #[IsGranted('ROLE_USER')] // Comentado temporalmente
    public function proposePrice(Request $request, int $id): JsonResponse
    {
        // Implementación simulada
        return $this->createCorsResponse([
            'success' => true,
            'message' => 'Precio propuesto con éxito'
        ]);
    }
    
    #[Route('/usuarios', name: 'usuarios_index', methods: ['GET'])]
    public function getUsuarios(UsuarioRepository $usuarioRepository, SerializerInterface $serializer): JsonResponse
    {
        try {
            // Obtiene todos los usuarios de la base de datos
            $usuarios = $usuarioRepository->findAll();
            
            // Transformamos los datos para la respuesta
            $result = [];
            foreach ($usuarios as $usuario) {
                $result[] = [
                    'id' => $usuario->getId_usuario(),
                    'username' => $usuario->getNombreUsuario(),
                    'email' => $usuario->getCorreo(),
                    'profesion' => $usuario->getProfesion(),
                    'descripcion' => $usuario->getDescripcion(),
                    'creditos' => $usuario->getCreditos(),
                    'avatarUrl' => $usuario->getFotoPerfil(),
                    'fechaRegistro' => $usuario->getFechaRegistro()->format('Y-m-d H:i:s')
                ];
            }
            
            return $this->createCorsResponse($result);
        } catch (\Exception $e) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Error al cargar usuarios desde la base de datos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 