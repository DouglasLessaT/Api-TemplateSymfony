<?php

namespace App\Controller\Api;

use App\Core\Domain\Exception\DomainException;
use App\Core\Presentation\Controller\BaseApiController;
use App\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth', name: 'api_auth_')]
class AuthController extends BaseApiController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['email', 'password', 'name']);

            $user = $this->authService->register(
                $data['email'],
                $data['password'],
                $data['name']
            );

            return $this->created($user->toArray(), 'Usuário registrado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao registrar usuário: ' . $e->getMessage());
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['email', 'password']);

            $result = $this->authService->login(
                $data['email'],
                $data['password']
            );

            return $this->success($result, 'Login realizado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao fazer login: ' . $e->getMessage());
        }
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): Response
    {
        try {
            $token = $request->headers->get('Authorization');
            
            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return $this->unauthorized('Token não fornecido');
            }

            $token = substr($token, 7);
            $result = $this->authService->refreshToken($token);

            return $this->success($result, 'Token renovado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao renovar token: ' . $e->getMessage());
        }
    }
}

