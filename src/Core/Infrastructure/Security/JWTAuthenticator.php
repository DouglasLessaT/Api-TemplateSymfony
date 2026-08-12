<?php

namespace App\Core\Infrastructure\Security;

use App\Domain\Entity\User;
use App\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * JWT Authenticator
 * 
 * Autenticador JWT para Symfony Security.
 */
class JWTAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Verifica se o authenticator suporta a requisição
     */
    public function supports(Request $request): ?bool
    {
        // Verifica se há um header Authorization com Bearer token
        return $request->headers->has('Authorization') &&
               str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    /**
     * Autentica o usuário
     */
    public function authenticate(Request $request): Passport
    {
        $token = $this->extractToken($request);
        
        if (!$token) {
            throw new AuthenticationException('Token não fornecido');
        }

        $user = $this->authService->validateToken($token);
        
        if (!$user) {
            throw new AuthenticationException('Token inválido ou expirado');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getId(), fn() => $user)
        );
    }

    /**
     * Extrai o token do header Authorization
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }

    /**
     * Resposta em caso de sucesso
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Continua a requisição normalmente
    }

    /**
     * Resposta em caso de falha
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'success' => false,
            'message' => 'Autenticação falhou',
            'error' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}

