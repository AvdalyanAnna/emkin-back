<?php

namespace App\Chat;

/**
 * Class Pagination
 * @package App\Chat
 */
class Pagination {

    /**
     * @var array
     */
    private array $paginationConfiguration;

    /**
     * Pagination constructor.
     */
    public function __construct() {
        $this->paginationConfiguration = ConfigurationManager::paginationDefaultParameters();
    }

    /**
     * @return int
     */
    public function getPage(): int {
        return $this->paginationConfiguration['page'];
    }

    /**
     * @return int
     */
    public function getPerPage(): int {
        return $this->paginationConfiguration['perPage'];
    }

    /**
     * @return string
     */
    public function getSorting(): string {
        return $this->paginationConfiguration['sorting'];
    }

    /**
     * @return array
     */
    public function getColumns(): array {
        return $this->paginationConfiguration['columns'];
    }

    /**
     * @return string
     */
    public function getPageName(): string {
        return $this->paginationConfiguration['pageName'];
    }

    /**
     * @return mixed
     */
    public function getUser(): mixed {
        return $this->paginationConfiguration['user'];
    }
}

return [
    'page' => $pagination['page'] ?? 1,
    'perPage' => $pagination['perPage'] ?? 25,
    'sorting' => $pagination['sorting'] ?? 'asc',
    'columns' => $pagination['columns'] ?? ['*'],
    'pageName' => $pagination['pageName'] ?? 'page',
    'user' => $pagination['user'] ?? null,
];
