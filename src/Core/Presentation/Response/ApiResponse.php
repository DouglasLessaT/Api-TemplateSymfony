<?php

namespace App\Core\Presentation\Response;

use App\Core\Application\Query\PaginatedResult;

/**
 * API Response
 * 
 * Formatador de respostas API padronizadas.
 */
class ApiResponse
{
    private function __construct(
        private bool $success,
        private mixed $data,
        private ?string $message,
        private array $errors,
        private array $meta
    ) {
    }

    /**
     * Cria uma resposta de sucesso
     */
    public static function success(mixed $data = null, string $message = 'Success', array $meta = []): self
    {
        return new self(true, $data, $message, [], $meta);
    }

    /**
     * Cria uma resposta de erro
     */
    public static function error(string $message, array $errors = [], array $meta = []): self
    {
        return new self(false, null, $message, $errors, $meta);
    }

    /**
     * Cria uma resposta paginada
     */
    public static function paginated(PaginatedResult $result, string $message = 'Success'): self
    {
        return new self(
            true,
            $result->getItems(),
            $message,
            [],
            $result->toArray()['pagination']
        );
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->success) {
            $response['data'] = $this->data;
        } else {
            $response['errors'] = $this->errors;
        }

        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        return $response;
    }

    /**
     * Converte para JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Adiciona metadados
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Define a mensagem
     */
    public function withMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }
}
