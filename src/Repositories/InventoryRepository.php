<?php

namespace App\Repositories;

use App\Core\Infrastructure\Repository\DoctrineRepository;
use App\Domain\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Inventory Repository
 */
class InventoryRepository extends DoctrineRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function getEntityClass(): string
    {
        return Inventory::class;
    }

    /**
     * Busca inventário por usuário
     */
    public function findByUserId(string $userId): ?Inventory
    {
        return $this->findOneBy(['userId' => $userId]);
    }

    /**
     * Busca todos os inventários de um usuário
     */
    public function findAllByUserId(string $userId): array
    {
        return $this->findBy(['userId' => $userId]);
    }
}

