<?php

namespace App\Console\Commands;

use App\Enums\Chat\ChatServiceNamesEnum;
use App\Models\Advertise;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->updateFiltersProducts();
    }

    public function updateFiltersProducts(){
        Advertise::query()->with('answers')->chunkById(100, function ($advertises) {
            foreach ($advertises as $advertise) {
                $advertise->answers->pluck('id')->toArray();
                $advertise->filters = $advertise->answers->pluck('id')->toArray();
                $advertise->save();
            }
        });
    }

    public function createConversation(): void {
        $participants = [User::find(5), User::find(4)];
//        \Chat::getInstance()
//            ->setStarter(User::find(4))
//            ->createConversation($participants, [], 11);

        \Chat::getInstance()
            ->conversation(Conversation::find(25))
            ->message('message')
            ->attachFiles([])
            ->from(User::find(5))
            ->send();

        \Chat::getInstance()
            ->setStarter(User::find(2))
            ->createConversation($participants, [], null, true)
            ->ticket()
            ->associate(Ticket::find(1))
            ->save();

//        \Chat::getInstance()
//            ->conversation(Conversation::find(24))
//            ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
//            ->changeModerator(User::find(2), User::find(22));

//        \Chat::getInstance()
//            ->conversation(Conversation::find(24))
//            ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
//            ->setParticipant(User::find(2))
//            ->readAll();
    }
}
