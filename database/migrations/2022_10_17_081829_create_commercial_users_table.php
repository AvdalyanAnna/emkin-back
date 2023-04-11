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
        Schema::create('commercial_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 400);
            $table->text('description');
            $table->float('price');
            $table->tinyInteger('status')->comment('Draft 0, Active 1, Closed 2');
            $table->foreignId('period_of_stay_id')->constrained()->cascadeOnDelete();
            $table->integer('count_days');
            $table->integer('gep_up');
            $table->integer('period_days');
            $table->integer('order')->default(0);
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
        Schema::dropIfExists('commercial_users');
    }
};
