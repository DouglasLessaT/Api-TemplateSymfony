<?php

namespace App\Service;

use App\Core\Domain\Exception\ValidationException;
use App\Domain\Entity\User;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * User Service
 * 
 * Serviço responsável por gerenciamento de usuários.
 */
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Busca usuário por ID
     */
    public function findById(string $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Busca usuário por email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Lista todos os usuários (apenas admin)
     */
    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Atualiza um usuário
     */
    public function update(User $user, array $data): User
    {
        if (isset($data['name']) && !empty($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['email']) && !empty($data['email'])) {
            if ($this->userRepository->emailExists($data['email'], $user->getId())) {
                throw ValidationException::fromErrors([
                    'email' => 'Email já está em uso'
                ]);
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                throw ValidationException::fromErrors([
                    'password' => 'Senha deve ter no mínimo 6 caracteres'
                ]);
            }
            $user->setPassword($data['password']);
        }

        $this->em->flush();

        return $user;
    }

    /**
     * Altera o tipo de usuário (apenas admin)
     */
    public function changeUserType(User $user, string $type): User
    {
        $user->setType($type);
        $this->em->flush();

        return $user;
    }

    /**
     * Ativa/desativa um usuário (apenas admin)
     */
    public function toggleActive(User $user, bool $isActive): User
    {
        $user->setIsActive($isActive);
        $this->em->flush();

        return $user;
    }

    /**
     * Deleta um usuário (apenas admin)
     */
    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}

