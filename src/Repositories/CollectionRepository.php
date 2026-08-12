<?php

namespace App\Repositories;

use App\Core\Infrastructure\Repository\DoctrineRepository;
use App\Domain\Entity\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Collection Repository
 */
class CollectionRepository extends DoctrineRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function getEntityClass(): string
    {
        return Collection::class;
    }

    /**
     * Busca coleções por inventário
     */
    public function findByInventoryId(string $inventoryId): array
    {
        return $this->repository->createQueryBuilder('c')
            ->join('c.inventory', 'i')
            ->where('i.id = :inventoryId')
            ->setParameter('inventoryId', $inventoryId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca coleções por jogo
     */
    public function findByGame(string $game): array
    {
        return $this->findBy(['game' => $game]);
    }
}

