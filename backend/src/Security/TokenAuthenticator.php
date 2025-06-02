<?php

namespace App\Security;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Psr\Log\LoggerInterface;

class TokenAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        $this->logger->info('Verificando soporte para autenticación', [
            'hasAuth' => $request->headers->has('Authorization'),
            'path' => $request->getPathInfo()
        ]);
        return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request): mixed
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $this->logger->info('Obteniendo credenciales', [
            'header' => $authorizationHeader
        ]);
        
        if (str_starts_with($authorizationHeader, 'Bearer ')) {
            return substr($authorizationHeader, 7);
        }
        
        return $authorizationHeader;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->getCredentials($request);
        $this->logger->info('Autenticando token', ['token' => $token]);
        
        if (null === $token) {
            throw new CustomUserMessageAuthenticationException('No se proporcionó token de API');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token) {
                $this->logger->info('Buscando usuario con token', ['token' => $token]);
                $usuario = $this->em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
                
                $this->logger->info('Resultado de búsqueda de usuario', ['usuario_encontrado' => $usuario ? $usuario->getId_usuario() : 'null']);
                
                if (!$usuario) {
                    $this->logger->warning('Usuario no encontrado para el token', ['token' => $token]);
                    throw new CustomUserMessageAuthenticationException('Token inválido o expirado');
                }

                $this->logger->info('Usuario encontrado', [
                    'id' => $usuario->getId_usuario(),
                    'nombre' => $usuario->getNombreUsuario()
                ]);

                return $usuario;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info('Autenticación exitosa', [
            'user' => $token->getUser()->getNombreUsuario()
        ]);
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->warning('Fallo de autenticación', [
            'message' => $exception->getMessage(),
            'path' => $request->getPathInfo()
        ]);

        $data = [
            'success' => false,
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $this->logger->warning('Inicio de autenticación requerida', [
            'path' => $request->getPathInfo()
        ]);

        $data = [
            'success' => false,
            'message' => 'Se requiere autenticación para acceder a este recurso'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
} 
