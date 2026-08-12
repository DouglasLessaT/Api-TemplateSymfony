<?php

namespace App\Core\Application\Query;

/**
 * Paginated Result
 * 
 * Encapsula resultados paginados.
 * 
 * @template T
 */
class PaginatedResult
{
    /**
     * @param T[] $items
     */
    public function __construct(
        private array $items,
        private int $total,
        private Pagination $pagination
    ) {
    }

    /**
     * @return T[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function getCurrentPage(): int
    {
        return $this->pagination->getPage();
    }

    public function getPerPage(): int
    {
        return $this->pagination->getPerPage();
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->pagination->getPerPage());
    }

    public function hasNextPage(): bool
    {
        return $this->getCurrentPage() < $this->getTotalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'pagination' => [
                'total' => $this->total,
                'currentPage' => $this->getCurrentPage(),
                'perPage' => $this->getPerPage(),
                'totalPages' => $this->getTotalPages(),
                'hasNextPage' => $this->hasNextPage(),
                'hasPreviousPage' => $this->hasPreviousPage(),
            ],
        ];
    }
}
