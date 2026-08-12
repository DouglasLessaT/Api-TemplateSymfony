<?php

namespace App\Core\Domain\ValueObject;

/**
 * String Value Object
 * 
 * Classe abstrata base para Value Objects baseados em string.
 */
abstract class StringValueObject implements ValueObject
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * Valida o valor (deve ser implementado pelas classes filhas)
     * 
     * @throws \InvalidArgumentException
     */
    abstract protected function validate(string $value): void;

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(?ValueObject $other): bool
    {
        if ($other === null) {
            return false;
        }

        if (!$other instanceof static) {
            return false;
        }

        return $this->value === $other->getValue();
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
