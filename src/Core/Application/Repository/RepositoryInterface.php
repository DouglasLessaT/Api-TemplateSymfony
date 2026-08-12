<?php

namespace App\Core\Application\Repository;

/**
 * Repository Interface
 * 
 * Interface genérica para repositórios seguindo o padrão Repository.
 * Define operações CRUD básicas.
 * 
 * @template T
 */
interface RepositoryInterface
{
    /**
     * Encontra uma entidade por ID
     * 
     * @param mixed $id
     * @return T|null
     */
    public function findById(mixed $id): ?object;

    /**
     * Encontra todas as entidades
     * 
     * @return T[]
     */
    public function findAll(): array;

    /**
     * Encontra entidades por critérios
     * 
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return T[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Encontra uma entidade por critérios
     * 
     * @param array $criteria
     * @return T|null
     */
    public function findOneBy(array $criteria): ?object;

    /**
     * Salva uma entidade
     * 
     * @param T $entity
     * @return T
     */
    public function save(object $entity): object;

    /**
     * Remove uma entidade
     * 
     * @param T $entity
     * @return void
     */
    public function remove(object $entity): void;

    /**
     * Conta entidades por critérios
     * 
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int;

    /**
     * Verifica se existe uma entidade com os critérios
     * 
     * @param array $criteria
     * @return bool
     */
    public function exists(array $criteria): bool;
}
