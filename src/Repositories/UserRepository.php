<?php

namespace App\Repositories;

use App\Core\Infrastructure\Repository\DoctrineRepository;
use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * User Repository
 */
class UserRepository extends DoctrineRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }

    /**
     * Busca usuário por email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Busca usuários por tipo
     */
    public function findByType(string $type): array
    {
        return $this->findBy(['type' => $type]);
    }

    /**
     * Busca usuários ativos
     */
    public function findActiveUsers(): array
    {
        return $this->findBy(['isActive' => true]);
    }

    /**
     * Verifica se email já existe
     */
    public function emailExists(string $email, ?string $excludeUserId = null): bool
    {
        $qb = $this->repository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        if ($excludeUserId) {
            $qb->andWhere('u.id != :excludeId')
                ->setParameter('excludeId', $excludeUserId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}

