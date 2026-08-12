<?php

namespace App\Core\Application\DTO;

use App\Core\Domain\Exception\ValidationException;

/**
 * Base DTO
 * 
 * Classe abstrata base para Data Transfer Objects.
 * Fornece métodos para conversão e validação.
 */
abstract class BaseDTO
{
    /**
     * Converte o DTO para array
     */
    abstract public function toArray(): array;

    /**
     * Cria um DTO a partir de um array
     * 
     * @param array $data
     * @return static
     * @throws ValidationException
     */
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            return new static();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            
            if (array_key_exists($name, $data)) {
                $params[] = $data[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $params[] = null;
            } else {
                throw ValidationException::fromErrors([
                    $name => "Required parameter '$name' is missing"
                ]);
            }
        }

        return new static(...$params);
    }

    /**
     * Valida o DTO
     * 
     * @return array Array de erros (vazio se válido)
     */
    public function validate(): array
    {
        // Implementação padrão - pode ser sobrescrita
        return [];
    }

    /**
     * Verifica se o DTO é válido
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Valida e lança exceção se inválido
     * 
     * @throws ValidationException
     */
    public function validateOrFail(): void
    {
        $errors = $this->validate();
        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
    }

    /**
     * Converte para JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Cria um DTO a partir de JSON
     * 
     * @throws ValidationException
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::fromErrors([
                'json' => 'Invalid JSON: ' . json_last_error_msg()
            ]);
        }

        return static::fromArray($data);
    }
}
