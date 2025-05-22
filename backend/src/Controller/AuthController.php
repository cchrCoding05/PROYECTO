<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em
    ) {}

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
}