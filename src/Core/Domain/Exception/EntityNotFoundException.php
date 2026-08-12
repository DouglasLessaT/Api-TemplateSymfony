<?php

namespace App\Core\Domain\Exception;

/**
 * Entity Not Found Exception
 * 
 * Lançada quando uma entidade não é encontrada.
 */
class EntityNotFoundException extends DomainException
{
    public static function forId(string $entityClass, mixed $id): self
    {
        return new self(
            sprintf('Entity %s with ID "%s" not found', $entityClass, $id),
            ['entity' => $entityClass, 'id' => $id],
            404
        );
    }

    public static function forCriteria(string $entityClass, array $criteria): self
    {
        return new self(
            sprintf('Entity %s not found with criteria: %s', $entityClass, json_encode($criteria)),
            ['entity' => $entityClass, 'criteria' => $criteria],
            404
        );
    }
}
