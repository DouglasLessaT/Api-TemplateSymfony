<?php

namespace App\Core\Domain\Event;

use DateTimeImmutable;

/**
 * Domain Event Interface
 * 
 * Interface base para eventos de domínio.
 * Eventos de domínio representam algo que aconteceu no domínio.
 */
interface DomainEvent
{
    /**
     * Retorna quando o evento ocorreu
     */
    public function occurredOn(): DateTimeImmutable;

    /**
     * Retorna o nome do evento
     */
    public function eventName(): string;

    /**
     * Converte o evento para array
     */
    public function toArray(): array;
}
