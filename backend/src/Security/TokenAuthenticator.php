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

class TokenAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request): mixed
    {
        $authorizationHeader = $request->headers->get('Authorization');
        
        if (str_starts_with($authorizationHeader, 'Bearer ')) {
            return substr($authorizationHeader, 7);
        }
        
        return $authorizationHeader;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->getCredentials($request);
        
        if (null === $token) {
            throw new CustomUserMessageAuthenticationException('No se proporcionó token de API');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token) {
                $usuario = $this->em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
                
                if (!$usuario) {
                    throw new CustomUserMessageAuthenticationException('Token inválido o expirado');
                }

                return $usuario;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'success' => false,
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            [
                'success' => false,
                'message' => 'Se requiere autenticación para acceder a este recurso'
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
} 