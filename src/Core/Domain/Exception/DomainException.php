<?php

namespace App\Core\Domain\Exception;

use Exception;

/**
 * Domain Exception
 * 
 * Exceção base para erros de domínio.
 * Representa violações de regras de negócio.
 */
class DomainException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = "", array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'type' => 'domain_error',
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'context' => $this->context,
        ];
    }
}
