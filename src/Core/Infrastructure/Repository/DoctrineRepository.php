<?php

namespace App\Core\Infrastructure\Repository;

use App\Core\Application\Repository\RepositoryInterface;
use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine Repository
 * 
 * Implementação base de repositório usando Doctrine ORM.
 * 
 * @template T of BaseEntity
 * @implements RepositoryInterface<T>
 */
abstract class DoctrineRepository implements RepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository($this->getEntityClass());
    }

    /**
     * Retorna a classe da entidade gerenciada por este repositório
     */
    abstract protected function getEntityClass(): string;

    public function findById(mixed $id): ?object
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?object
    {
        return $this->repository->findOneBy($criteria);
    }

    public function save(object $entity): object
    {
        if ($entity instanceof BaseEntity) {
            $entity->markAsUpdated();
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function count(array $criteria = []): int
    {
        return $this->repository->count($criteria);
    }

    public function exists(array $criteria): bool
    {
        return $this->count($criteria) > 0;
    }

    /**
     * Salva múltiplas entidades em batch
     * 
     * @param T[] $entities
     */
    public function saveAll(array $entities): void
    {
        foreach ($entities as $entity) {
            if ($entity instanceof BaseEntity) {
                $entity->markAsUpdated();
            }
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * Remove múltiplas entidades em batch
     * 
     * @param T[] $entities
     */
    public function removeAll(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * Limpa o EntityManager
     */
    public function clear(): void
    {
        $this->entityManager->clear();
    }

    /**
     * Atualiza uma entidade do banco de dados
     */
    public function refresh(object $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    /**
     * Retorna o EntityManager
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
