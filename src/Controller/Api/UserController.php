<?php

namespace App\Controller\Api;

use App\Core\Domain\Exception\DomainException;
use App\Core\Domain\Exception\EntityNotFoundException;
use App\Core\Presentation\Controller\BaseApiController;
use App\Domain\Entity\User;
use App\Service\AuthService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users', name: 'api_users_')]
class UserController extends BaseApiController
{
    public function __construct(
        private UserService $userService,
        private AuthService $authService
    ) {
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            return $this->success($user->toArray());
        } catch (\Exception $e) {
            return $this->error('Erro ao buscar usuário: ' . $e->getMessage());
        }
    }

    #[Route('/me', name: 'update_me', methods: ['PUT', 'PATCH'])]
    public function updateMe(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->unauthorized();
            }

            $data = $this->getRequestData($request);
            $updatedUser = $this->userService->update($user, $data);

            return $this->success($updatedUser->toArray(), 'Usuário atualizado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user || !$user->canManageUsers()) {
                return $this->forbidden('Apenas administradores podem listar usuários');
            }

            $users = $this->userService->findAll();
            $usersData = array_map(fn(User $u) => $u->toArray(), $users);

            return $this->success($usersData);
        } catch (\Exception $e) {
            return $this->error('Erro ao listar usuários: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id, Request $request): Response
    {
        try {
            $currentUser = $this->getCurrentUser($request);
            
            if (!$currentUser) {
                return $this->unauthorized();
            }

            // Usuário pode ver seu próprio perfil ou admin pode ver qualquer perfil
            if ($currentUser->getId() !== $id && !$currentUser->canManageUsers()) {
                return $this->forbidden('Você não tem permissão para ver este usuário');
            }

            $user = $this->userService->findById($id);
            
            if (!$user) {
                return $this->notFound('Usuário não encontrado');
            }

            return $this->success($user->toArray());
        } catch (\Exception $e) {
            return $this->error('Erro ao buscar usuário: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request): Response
    {
        try {
            $currentUser = $this->getCurrentUser($request);
            
            if (!$currentUser || !$currentUser->canManageUsers()) {
                return $this->forbidden('Apenas administradores podem atualizar outros usuários');
            }

            $user = $this->userService->findById($id);
            
            if (!$user) {
                return $this->notFound('Usuário não encontrado');
            }

            $data = $this->getRequestData($request);
            
            // Permite alterar tipo apenas para admin
            if (isset($data['type']) && $data['type'] !== $user->getType()) {
                $user = $this->userService->changeUserType($user, $data['type']);
            }

            // Permite ativar/desativar apenas para admin
            if (isset($data['isActive']) && $data['isActive'] !== $user->isActive()) {
                $user = $this->userService->toggleActive($user, $data['isActive']);
            }

            $updatedUser = $this->userService->update($user, $data);

            return $this->success($updatedUser->toArray(), 'Usuário atualizado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id, Request $request): Response
    {
        try {
            $currentUser = $this->getCurrentUser($request);
            
            if (!$currentUser || !$currentUser->canManageUsers()) {
                return $this->forbidden('Apenas administradores podem deletar usuários');
            }

            $user = $this->userService->findById($id);
            
            if (!$user) {
                return $this->notFound('Usuário não encontrado');
            }

            $this->userService->delete($user);

            return $this->noContent();
        } catch (\Exception $e) {
            return $this->error('Erro ao deletar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Obtém o usuário atual a partir do token JWT
     */
    private function getCurrentUser(Request $request): ?User
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return null;
        }

        $token = substr($token, 7);
        return $this->authService->validateToken($token);
    }
}

