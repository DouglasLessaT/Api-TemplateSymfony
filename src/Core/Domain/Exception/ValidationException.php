<?php

namespace App\Core\Domain\Exception;

/**
 * Validation Exception
 * 
 * Lançada quando há erros de validação.
 */
class ValidationException extends DomainException
{
    private array $errors = [];

    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 422)
    {
        parent::__construct($message, ['errors' => $errors], $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function fromErrors(array $errors): self
    {
        return new self("Validation failed", $errors);
    }

    public function toArray(): array
    {
        return [
            'type' => 'validation_error',
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ];
    }
}
