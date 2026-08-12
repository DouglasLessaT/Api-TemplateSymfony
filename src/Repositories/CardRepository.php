<?php

namespace App\Repositories;

use App\Core\Infrastructure\Repository\DoctrineRepository;
use App\Domain\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;

/**
 * CardRepository
 * 
 * Repositório para gerenciar cards no banco de dados.
 * Suporta busca por jogo, set e número do card.
 */
class CardRepository extends DoctrineRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    protected function getEntityClass(): string
    {
        return Card::class;
    }

    /**
     * Encontra um card por jogo, set e número
     * 
     * @param string $game Tipo do jogo: 'mtg', 'pokemon', 'onepiece'
     * @param string $setCode Código do set
     * @param string $number Número do card no set
     * @return Card|null
     */
    public function findBySetAndNumber(string $game, string $setCode, string $number): ?Card
    {
        // Como Card é uma classe abstrata, precisamos buscar nas classes filhas específicas
        // O Doctrine com Single Table Inheritance ou Joined Table Inheritance
        // permite buscar pela classe base, mas precisamos especificar a classe concreta
        
        $entityClass = match (strtolower($game)) {
            'mtg', 'magic' => \App\Domain\Entity\CardMTG::class,
            'pokemon', 'ptcg' => \App\Domain\Entity\CardPTCG::class,
            'onepiece', 'opcg' => \App\Domain\Entity\CardOPCG::class,
            default => null,
        };

        if ($entityClass === null) {
            return null;
        }

        // Buscar usando a classe concreta
        $repository = $this->entityManager->getRepository($entityClass);

        // Construir critérios baseado no tipo de jogo
        $criteria = ['game' => $game];
        
        if ($game === 'mtg' || $game === 'onepiece') {
            $criteria['setCode'] = $setCode;
            if ($game === 'mtg') {
                $criteria['collectorNumber'] = $number;
            } else {
                $criteria['cardNumber'] = $number;
            }
        } elseif ($game === 'pokemon') {
            $criteria['setId'] = $setCode;
            $criteria['number'] = $number;
        }

        return $repository->findOneBy($criteria);
    }

    /**
     * Encontra cards por jogo
     * 
     * @param string $game Tipo do jogo
     * @param int|null $limit
     * @param int|null $offset
     * @return Card[]
     */
    public function findByGame(string $game, ?int $limit = null, ?int $offset = null): array
    {
        return $this->findBy(['game' => $game], ['name' => 'ASC'], $limit, $offset);
    }

    /**
     * Encontra cards por set
     * 
     * @param string $game Tipo do jogo
     * @param string $setCode Código do set
     * @return Card[]
     */
    public function findBySet(string $game, string $setCode): array
    {
        $entityClass = match (strtolower($game)) {
            'mtg', 'magic' => \App\Domain\Entity\CardMTG::class,
            'pokemon', 'ptcg' => \App\Domain\Entity\CardPTCG::class,
            'onepiece', 'opcg' => \App\Domain\Entity\CardOPCG::class,
            default => null,
        };

        if ($entityClass === null) {
            return [];
        }

        $repository = $this->entityManager->getRepository($entityClass);
        
        $criteria = ['game' => $game];
        if ($game === 'mtg' || $game === 'onepiece') {
            $criteria['setCode'] = $setCode;
        } elseif ($game === 'pokemon') {
            $criteria['setId'] = $setCode;
        }

        return $repository->findBy($criteria);
    }

    /**
     * Conta cards por jogo
     * 
     * @param string $game
     * @return int
     */
    public function countByGame(string $game): int
    {
        return $this->count(['game' => $game]);
    }

    /**
     * Verifica se um card existe no banco
     * 
     * @param string $game
     * @param string $setCode
     * @param string $number
     * @return bool
     */
    public function existsBySetAndNumber(string $game, string $setCode, string $number): bool
    {
        return $this->findBySetAndNumber($game, $setCode, $number) !== null;
    }
}

