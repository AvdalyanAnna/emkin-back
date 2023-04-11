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
        Schema::create('commercial_notifications', function (Blueprint $table) {
            $table->id();
            $table->boolean('text');
            $table->string('title', 400);
            $table->text('description');
            $table->string('link')->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('status')->comment('Draft 0, Active 1');
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
        Schema::dropIfExists('commercial_notifications');
    }
};
