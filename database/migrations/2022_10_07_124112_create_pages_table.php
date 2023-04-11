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
        $columns = ['locale', 'type', 'page_key'];
        Schema::create('pages', function (Blueprint $table) use ($columns) {
            $table->id();
            $table->string('locale');
            $table->string('type',50);
            $table->string('name')->nullable();
            $table->string('page_key')->unique();
            $table->text('content');
            $table->boolean('status')->default(1);
            $table->index($columns);
            $table->unique($columns);
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
        Schema::dropIfExists('pages');
    }
};
