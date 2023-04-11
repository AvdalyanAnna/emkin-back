<?php

namespace App\Services\V1\Admin;


use App\Repositories\V1\Admin\Support\TicketRepository;

/**
 * Class SupportService
 * @package App\Services\V1\Admin
 */
class SupportService {

    /**
     * SupportService constructor.
     * @param TicketRepository $ticketRepository
     */
    public function __construct(
        private TicketRepository $ticketRepository,
    ) { }

    /**
     * @return TicketRepository
     */
    public function ticket(): TicketRepository {
        return $this->ticketRepository;
    }
}
