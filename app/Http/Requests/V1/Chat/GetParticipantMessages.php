<?php

namespace App\Http\Requests\V1\Chat;

use App\Chat\Pagination;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class GetParticipantMessages
 * @package App\Http\Requests\V1\Chat
 */
class GetParticipantMessages extends BaseRequest
{

    /**
     * GetParticipantMessages constructor.
     * @param Pagination $pagination
     */
    public function __construct(private Pagination $pagination) { parent::__construct(); }

    /**
     * @return bool
     */
    public function authorized(): bool { return true; }

    /**
     * @return string[]
     */
    #[ArrayShape([
        'participant_id' => "string",
        'participant_type' => "string",
        'page' => "string",
        'perPage' => "string",
        'sorting' => "string",
        'columns' => "string",
        'pageName' => "string"
    ])] public function rules(): array {
        return [
            'participant_id'   => 'required',
            'participant_type' => 'required',
            'page'             => 'integer',
            'perPage'          => 'integer',
            'sorting'          => 'string|in:asc,desc',
            'columns'          => 'array',
            'pageName'         => 'string',
        ];
    }

    /**
     * @return array
     */
    public function getPaginationParams(): array {
        return [
            'page'     => $this->page ?? $this->pagination->getPage(),
            'perPage'  => $this->perPage ?? $this->pagination->getPerPage(),
            'sorting'  => $this->sorting ?? $this->pagination->getSorting(),
            'columns'  => $this->columns ?? $this->pagination->getColumns(),
            'pageName' => $this->pageName ?? $this->pagination->getPageName(),
            'user' => $this->user ?? $this->pagination->getUser(),
        ];
    }
}
