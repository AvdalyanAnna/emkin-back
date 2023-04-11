<?php

namespace App\Console\Commands;

use App\Enums\Users\TicketStatuses;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Class SupportTicketCloseCommand
 * @package App\Console\Commands
 */
class SupportTicketCloseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'close:ticked';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Если Пользователь не ответил поддержку в течение 2 суток билет автоматически закрывается';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $ticketsClosedCount = 0;
        $dateTime = Carbon::now()->subDays(2)->toDateTimeString();

        Ticket::query()
            ->where('status', '=', TicketStatuses::VIEWED)
            ->where('updated_at', '<=', $dateTime)
            ->chunk(1000, function ($tickets) use (&$ticketsClosedCount) {
                foreach ($tickets as $ticket) {
                    $ticket->update(['status' => TicketStatuses::CLOSE]);
                    $ticketsClosedCount++;
                }
            });

        $message = "Closed Ticket Count => $ticketsClosedCount";

        $this->info($message);
        \Log::info($message);

        return Command::SUCCESS;
    }
}
