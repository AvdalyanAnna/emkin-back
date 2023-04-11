<?php

namespace App\Traits\Chat;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Trait Paginates
 * @package App\Traits\Chat
 */
trait Paginates
{
    /**
     * @var int
     */
    protected int $perPage = 25;

    /**
     * @var int
     */
    protected int $page = 1;

    /**
     * @var string
     */
    protected string $sorting = 'asc';

    /**
     * @var string[]
     */
    protected array $columns = ['*'];

    /**
     * @var string
     */
    protected string $pageName = 'page';

    /**
     * @var bool
     */
    protected bool $deleted = false;

    /**
     * Set the limit.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): static {
        $this->perPage = $limit ?: $this->perPage;

        return $this;
    }

    /**
     * Set current page for pagination.
     *
     * @param int $page
     *
     * @return static
     */
    public function page(int $page): static {
        $this->page = $page ?: $this->page;

        return $this;
    }

    /**
     * @param int $perPage
     * @return $this
     */
    public function perPage(int $perPage): static {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @return static
     */
    public function deleted(): static {
        $this->deleted = true;
        return $this;
    }

    /**
     * @param $params
     * @return $this
     */
    public function setPaginationParams($params): static {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    #[ArrayShape([
        'page' => "int",
        'perPage' => "int",
        'sorting' => "string",
        'columns' => "string[]",
        'pageName' => "string"
    ])] public function getPaginationParams(): array {
        return [
            'page'     => $this->page,
            'perPage'  => $this->perPage,
            'sorting'  => $this->sorting,
            'columns'  => $this->columns,
            'pageName' => $this->pageName,
        ];
    }
}
