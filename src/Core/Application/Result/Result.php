<?php

namespace App\Core\Application\Result;

/**
 * Result
 * 
 * Encapsula o resultado de uma operação (Success/Failure pattern).
 * Evita o uso de exceções para controle de fluxo.
 * 
 * @template T
 */
class Result
{
    private bool $success;
    private mixed $value;
    private ?string $error;
    private array $errors;
    private array $metadata;

    private function __construct(
        bool $success,
        mixed $value = null,
        ?string $error = null,
        array $errors = [],
        array $metadata = []
    ) {
        $this->success = $success;
        $this->value = $value;
        $this->error = $error;
        $this->errors = $errors;
        $this->metadata = $metadata;
    }

    /**
     * Cria um resultado de sucesso
     * 
     * @param mixed $value
     * @param array $metadata
     * @return self
     */
    public static function success(mixed $value = null, array $metadata = []): self
    {
        return new self(true, $value, null, [], $metadata);
    }

    /**
     * Cria um resultado de falha
     * 
     * @param string $error
     * @param array $errors
     * @param array $metadata
     * @return self
     */
    public static function failure(string $error, array $errors = [], array $metadata = []): self
    {
        return new self(false, null, $error, $errors, $metadata);
    }

    /**
     * Verifica se a operação foi bem-sucedida
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Verifica se a operação falhou
     */
    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * Retorna o valor (apenas se sucesso)
     * 
     * @return T
     * @throws \RuntimeException
     */
    public function getValue(): mixed
    {
        if (!$this->success) {
            throw new \RuntimeException('Cannot get value from a failed result');
        }

        return $this->value;
    }

    /**
     * Retorna o valor ou um valor padrão
     * 
     * @param T $default
     * @return T
     */
    public function getValueOr(mixed $default): mixed
    {
        return $this->success ? $this->value : $default;
    }

    /**
     * Retorna a mensagem de erro (apenas se falha)
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Retorna os erros detalhados
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna os metadados
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Executa uma função se o resultado for sucesso
     * 
     * @param callable $callback
     * @return self
     */
    public function onSuccess(callable $callback): self
    {
        if ($this->success) {
            $callback($this->value);
        }

        return $this;
    }

    /**
     * Executa uma função se o resultado for falha
     * 
     * @param callable $callback
     * @return self
     */
    public function onFailure(callable $callback): self
    {
        if (!$this->success) {
            $callback($this->error, $this->errors);
        }

        return $this;
    }

    /**
     * Mapeia o valor para outro tipo
     * 
     * @template U
     * @param callable $mapper
     * @return Result<U>
     */
    public function map(callable $mapper): self
    {
        if (!$this->success) {
            return $this;
        }

        return self::success($mapper($this->value), $this->metadata);
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'value' => $this->value,
            'error' => $this->error,
            'errors' => $this->errors,
            'metadata' => $this->metadata,
        ];
    }
}
