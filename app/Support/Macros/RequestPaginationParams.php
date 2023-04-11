<?php

namespace App\Support\Macros;

use App\Chat\Pagination;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

/**
 * Class RequestPaginationParams
 * @package App\Support\Macros
 */
class RequestPaginationParams {

    /**
     * RequestPaginationParams constructor.
     * @param Request $request
     * @param Pagination $pagination
     */
    public function __construct(private Request $request, private Pagination $pagination) { }

    /**
     * @throws Throwable
     * @return array
     */
    #[ArrayShape([
        'page' => "mixed",
        'perPage' => "mixed",
        'sorting' => "mixed",
        'columns' => "mixed",
        'pageName' => "mixed"
    ])] public function __invoke(): array {

        $validator = \Validator::make($this->request->all(), [
            'page' => 'integer',
            'perPage' => 'integer',
            'sorting' => 'string|in:asc,desc',
            'columns' => 'array',
            'pageName' => 'string',
            'user' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return [
            'page' => $this->request->get('page') ?? $this->pagination->getPage(),
            'perPage' => $this->request->get('perPage') ?? $this->pagination->getPerPage(),
            'sorting' => $this->request->get('sorting') ?? $this->pagination->getSorting(),
            'columns' => $this->request->get('columns') ?? $this->pagination->getColumns(),
            'pageName' => $this->request->get('pageName') ?? $this->pagination->getPageName(),
            'user' => $this->request->get('user') ?? $this->pagination->getUser(),
        ];
    }
}
