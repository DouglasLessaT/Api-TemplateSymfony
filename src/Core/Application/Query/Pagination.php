<?php

namespace App\Core\Application\Query;

/**
 * Pagination
 * 
 * Representa parâmetros de paginação.
 */
class Pagination
{
    private int $page;
    private int $perPage;
    private int $offset;

    public function __construct(int $page = 1, int $perPage = 20)
    {
        $this->page = max(1, $page);
        $this->perPage = max(1, min($perPage, 100)); // Máximo 100 itens por página
        $this->offset = ($this->page - 1) * $this->perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    /**
     * Cria a partir de parâmetros de query
     */
    public static function fromQuery(array $query): self
    {
        $page = (int) ($query['page'] ?? 1);
        $perPage = (int) ($query['per_page'] ?? $query['perPage'] ?? 20);

        return new self($page, $perPage);
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'offset' => $this->offset,
            'limit' => $this->perPage,
        ];
    }
}
