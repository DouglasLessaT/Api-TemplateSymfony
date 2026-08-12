<?php

namespace App\Core\Domain\ValueObject;

/**
 * Value Object Interface
 * 
 * Interface base para Value Objects.
 * Value Objects são imutáveis e comparados por valor, não por identidade.
 */
interface ValueObject
{
    /**
     * Compara este Value Object com outro
     * 
     * @param ValueObject|null $other
     * @return bool
     */
    public function equals(?ValueObject $other): bool;

    /**
     * Retorna a representação em string do Value Object
     * 
     * @return string
     */
    public function toString(): string;

    /**
     * Converte o Value Object para array
     * 
     * @return array
     */
    public function toArray(): array;
}
