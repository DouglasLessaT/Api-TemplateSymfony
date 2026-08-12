<?php

namespace App\Core\Domain\Event;

use DateTimeImmutable;

/**
 * Base Domain Event
 * 
 * Implementação base para eventos de domínio.
 */
abstract class BaseDomainEvent implements DomainEvent
{
    private DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventName(): string
    {
        return static::class;
    }

    public function toArray(): array
    {
        return [
            'eventName' => $this->eventName(),
            'occurredOn' => $this->occurredOn->format('c'),
        ];
    }
}
