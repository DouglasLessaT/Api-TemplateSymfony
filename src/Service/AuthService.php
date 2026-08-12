<?php

namespace App\Service;

use App\Core\Domain\Exception\ValidationException;
use App\Core\Infrastructure\Security\JWTManager;
use App\Domain\Entity\User;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Auth Service
 * 
 * Serviço responsável por autenticação e geração de tokens JWT.
 */
class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private JWTManager $jwtManager,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Registra um novo usuário
     */
    public function register(string $email, string $password, string $name): User
    {
        // Validações
        if ($this->userRepository->emailExists($email)) {
            throw ValidationException::fromErrors([
                'email' => 'Email já está em uso'
            ]);
        }

        if (strlen($password) < 6) {
            throw ValidationException::fromErrors([
                'password' => 'Senha deve ter no mínimo 6 caracteres'
            ]);
        }

        if (empty($name)) {
            throw ValidationException::fromErrors([
                'name' => 'Nome é obrigatório'
            ]);
        }

        // Criar usuário
        $user = new User($email, $password, $name);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Autentica um usuário e retorna o token JWT
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::fromErrors([
                'credentials' => 'Email ou senha inválidos'
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::fromErrors([
                'account' => 'Conta desativada'
            ]);
        }

        if (!$user->verifyPassword($password)) {
            throw ValidationException::fromErrors([
                'credentials' => 'Email ou senha inválidos'
            ]);
        }

        // Atualizar último login
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->em->flush();

        // Gerar token JWT
        $token = $this->jwtManager->generate([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'type' => $user->getType(),
            'name' => $user->getName(),
        ]);

        return [
            'token' => $token,
            'user' => $user->toArray(),
        ];
    }

    /**
     * Valida um token JWT e retorna o usuário
     */
    public function validateToken(string $token): ?User
    {
        $payload = $this->jwtManager->decode($token);

        if (!$payload || !isset($payload['sub'])) {
            return null;
        }

        $user = $this->userRepository->find($payload['sub']);

        if (!$user || !$user->isActive()) {
            return null;
        }

        return $user;
    }

    /**
     * Gera um refresh token
     */
    public function refreshToken(string $token): array
    {
        $user = $this->validateToken($token);

        if (!$user) {
            throw ValidationException::fromErrors([
                'token' => 'Token inválido'
            ]);
        }

        $newToken = $this->jwtManager->generate([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'type' => $user->getType(),
            'name' => $user->getName(),
        ]);

        return [
            'token' => $newToken,
            'user' => $user->toArray(),
        ];
    }
}

