<?php

namespace App\Console\Commands;

use App\Enums\Advertise\AdvertiseStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckActiveAdvertises extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:in_active_advertises';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'следиыт за датой объявления и автоматически меняет статус созданного объявления спустя 30 дней на “не активно“';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inactiveDays = 30;

        DB::table('advertises')
            ->whereIn('status', [AdvertiseStatus::NotVerified, AdvertiseStatus::Active])
            ->where([
                ['auto-renewal', '=', true],
                ['inactively_date', '<=', Carbon::now()]
            ])
            ->update(['inactively_date' => Carbon::now()->addDays($inactiveDays)]);

        DB::table('advertises')
            ->whereIn('status', [AdvertiseStatus::NotVerified, AdvertiseStatus::Active])
            ->where([
                ['auto-renewal', '=', false],
                ['inactively_date', '<=', Carbon::now()]
            ])
            ->update(['status' => AdvertiseStatus::InActive]);

        return 0;
    }
}
