<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Professional;
use App\Entity\Product;
use App\Entity\CreditTransaction;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    private $em;
    private $passwordHasher;
    
    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }
    
    private function createCorsResponse($data = null, $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
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
    
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validación básica
        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Los campos de usuario y contraseña son obligatorios'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Verificar si el usuario ya existe
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Este nombre de usuario ya está en uso'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Crear nuevo usuario
        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        
        // Créditos iniciales
        $user->setCredits(500);
        
        // Rol por defecto
        $user->setRoles(['ROLE_USER']);
        
        // Si se ha proporcionado una profesión, crear y asociar un perfil de profesional
        if (isset($data['profession']) && !empty($data['profession'])) {
            $professional = new Professional();
            $professional->setUser($user);
            $professional->setProfession($data['profession']);
            $professional->setRating(0);
            $professional->setRatingCount(0);
            
            $this->em->persist($professional);
        }
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $this->createCorsResponse([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'token' => 'dummy_token_' . $user->getId() // En una implementación real, generarías un JWT
        ], Response::HTTP_CREATED);
    }
    
    #[Route('/login_check', name: 'login_check', methods: ['POST'])]
    public function loginCheck(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Los campos de usuario y contraseña son obligatorios'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Buscar usuario por nombre de usuario
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        
        // Verificar si el usuario existe y la contraseña es correcta
        // En una aplicación real, usarías $passwordHasher->isPasswordValid()
        if (!$user || $user->getPassword() !== $this->passwordHasher->hashPassword($user, $data['password'])) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        // Devolver token (simulado en este caso)
        return $this->createCorsResponse([
            'success' => true,
            'token' => 'dummy_token_' . $user->getId(), // En una implementación real, generarías un JWT
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'credits' => $user->getCredits()
            ]
        ]);
    }
    
    #[Route('/user/current', name: 'current_user', methods: ['GET'])]
    public function getCurrentUser(Request $request): JsonResponse
    {
        // Simulación: en una app real, extraerías el usuario del token
        // Para fines de demostración, simplemente devolvemos un usuario de prueba
        return $this->createCorsResponse([
            'id' => 1,
            'username' => 'usuario_demo',
            'credits' => 500,
            'roles' => ['ROLE_USER'],
            'description' => 'Este es un usuario de prueba',
            'avatarUrl' => null,
            'profession' => 'Desarrollador'
        ]);
    }
    
    #[Route('/user/profile', name: 'update_profile', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['description'])) {
            $user->setDescription($data['description']);
        }
        
        $this->em->flush();
        
        return $this->createCorsResponse([
            'success' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);
    }
    
    #[Route('/professionals/search', name: 'search_professionals', methods: ['GET'])]
    public function searchProfessionals(Request $request): JsonResponse
    {
        // Datos de muestra para profesionales
        $professionals = [
            [
                'id' => 1,
                'name' => 'Mr. El Mejor fontanero del mundo',
                'profession' => 'Fontanero',
                'rating' => 5,
                'ratingCount' => 10000,
                'description' => 'Fontanero con más de 10 años de experiencia',
                'avatarUrl' => null
            ],
            [
                'id' => 2,
                'name' => 'Super Mario',
                'profession' => 'Fontanero',
                'rating' => 4,
                'ratingCount' => 8000,
                'description' => 'Especializado en tuberías y rescate de princesas',
                'avatarUrl' => null
            ]
        ];
        
        // Filtrar por consulta si se proporciona
        $query = $request->query->get('q', '');
        if (!empty($query)) {
            $professionals = array_filter($professionals, function($professional) use ($query) {
                return stripos($professional['name'], $query) !== false || 
                       stripos($professional['profession'], $query) !== false;
            });
        }
        
        return $this->createCorsResponse(array_values($professionals));
    }
    
    #[Route('/products/search', name: 'search_products', methods: ['GET'])]
    public function searchProducts(Request $request): JsonResponse
    {
        // Datos de muestra para productos
        $products = [
            [
                'id' => 1,
                'name' => 'Bicicleta',
                'description' => 'Bicicleta en buen estado',
                'price' => 1000,
                'imageUrl' => 'https://via.placeholder.com/150',
                'seller' => [
                    'id' => 1,
                    'username' => 'Super Mario',
                    'sales' => 24
                ]
            ],
            [
                'id' => 2,
                'name' => 'Bicicleta antigua',
                'description' => 'Bicicleta clásica en perfecto estado',
                'price' => 876,
                'imageUrl' => 'https://via.placeholder.com/150',
                'seller' => [
                    'id' => 2,
                    'username' => 'Super Luigi Bros',
                    'sales' => 15
                ]
            ]
        ];
        
        // Filtrar por consulta si se proporciona
        $query = $request->query->get('q', '');
        if (!empty($query)) {
            $products = array_filter($products, function($product) use ($query) {
                return stripos($product['name'], $query) !== false || 
                       stripos($product['description'], $query) !== false;
            });
        }
        
        return $this->createCorsResponse(array_values($products));
    }
    
    #[Route('/products/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(int $id): JsonResponse
    {
        // Datos de muestra para un producto específico
        $product = [
            'id' => $id,
            'name' => 'Bicicleta',
            'description' => 'Bicicleta en buen estado',
            'price' => 1000,
            'imageUrl' => 'https://via.placeholder.com/300x200',
            'seller' => [
                'id' => 1,
                'username' => 'SuperMario64',
                'sales' => 24
            ]
        ];
        
        return $this->createCorsResponse($product);
    }
    
    #[Route('/credits/balance', name: 'get_balance', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getBalance(): JsonResponse
    {
        $user = $this->getUser();
        
        return $this->createCorsResponse([
            'credits' => $user->getCredits()
        ]);
    }
    
    #[Route('/products/{id}/propose-price', name: 'propose_price', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function proposePrice(Request $request, int $id): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['price']) || !is_numeric($data['price'])) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Se requiere un precio válido'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $product = $this->em->getRepository(Product::class)->find($id);
        if (!$product) {
            return $this->createCorsResponse([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Aquí se implementaría la lógica para guardar la propuesta
        // Por ejemplo, se podría crear una entidad PriceProposal
        
        return $this->createCorsResponse([
            'success' => true,
            'message' => 'Propuesta de precio enviada correctamente'
        ]);
    }
} 