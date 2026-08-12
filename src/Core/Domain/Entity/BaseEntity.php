<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Event\DomainEvent;
use DateTimeImmutable;

/**
 * Base Entity Class
 * 
 * Classe abstrata base para todas as entidades do domínio.
 * Fornece funcionalidades comuns como ID, timestamps e eventos de domínio.
 */
abstract class BaseEntity
{
    protected mixed $id = null;
    protected DateTimeImmutable $createdAt;
    protected DateTimeImmutable $updatedAt;

    /** @var DomainEvent[] */
    private array $domainEvents = [];

    public function __construct()
    {
        $this->id = \Symfony\Component\Uid\Uuid::v4();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Marca a entidade como atualizada
     */
    public function markAsUpdated(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Adiciona um evento de domínio
     */
    protected function addDomainEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Retorna e limpa todos os eventos de domínio
     * 
     * @return DomainEvent[]
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    /**
     * Verifica se a entidade é igual a outra
     */
    public function equals(?BaseEntity $other): bool
    {
        if ($other === null) {
            return false;
        }

        if (!$other instanceof static) {
            return false;
        }

        return $this->id !== null && $this->id === $other->getId();
    }

    /**
     * Converte a entidade para array
     */
    public function toArray(): array
    {
        $id = $this->id;
        if ($id instanceof \Symfony\Component\Uid\Uuid) {
            $id = $id->toRfc4122();
        }
        
        return [
            'id' => $id,
            'createdAt' => isset($this->createdAt) ? $this->createdAt->format('c') : null,
            'updatedAt' => isset($this->updatedAt) ? $this->updatedAt->format('c') : null,
        ];
    }

    /**
     * Representação em string da entidade
     */
    public function __toString(): string
    {
        return static::class . '#' . ($this->id ?? 'new');
    }
}
