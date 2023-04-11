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
        Schema::create('advertise_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('advertise_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('type')
                ->index()
                ->comment('Счетчик кнопки с телефоном 0, Счетчик просмотра страички 1, Счетчик добавления в избранное 2');

            $table->unique(['user_id', 'advertise_id', 'type']);

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
        Schema::dropIfExists('advertise_statistics');
    }
};
