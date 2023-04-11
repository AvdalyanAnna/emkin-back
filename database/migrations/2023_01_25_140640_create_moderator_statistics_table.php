<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moderator_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('type')
                ->comment('В классе ModeratorStatisticsEnum написано что означает эти цифры');
            $table->foreignId('moderator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('advertise_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('banned_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('unbanned_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moderator_statistics');
    }
};
