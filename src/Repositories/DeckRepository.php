<?php

namespace App\Repositories;

use App\Core\Infrastructure\Repository\DoctrineRepository;
use App\Domain\Entity\Deck;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Deck Repository
 */
class DeckRepository extends DoctrineRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function getEntityClass(): string
    {
        return Deck::class;
    }

    /**
     * Busca decks por inventário
     */
    public function findByInventoryId(string $inventoryId): array
    {
        return $this->repository->createQueryBuilder('d')
            ->join('d.inventory', 'i')
            ->where('i.id = :inventoryId')
            ->setParameter('inventoryId', $inventoryId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca decks por jogo
     */
    public function findByGame(string $game): array
    {
        return $this->findBy(['game' => $game]);
    }
}

