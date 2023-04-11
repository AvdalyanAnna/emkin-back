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
        Schema::create('refusals', function (Blueprint $table) {
            $table->id();
            $table->text('refusal');
            $table->tinyInteger('type')
                ->comment('Тип 0 это Виды Текста Для Модераторов когда отклоняют объявление, Тип 1 Это типы Жалоб Для Чата Пользователей');
            $table->integer('order');

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
        Schema::dropIfExists('refusals');
    }
};
