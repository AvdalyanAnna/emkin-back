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
        Schema::create('commercial_businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->tinyInteger('type')
                ->default(false)
                ->index()
                ->comment('указываем только превьюшку и опциями правим внешний вид (Banner Vertical) type => 0, когда указываем полностью изображение баннера (Horizontal). type => 1');

            $table->string('link');
            $table->string('location')->nullable();
            $table->tinyInteger('status')->comment('Draft 0, Active 1, Closed 2')->index();
            $table->text('details')->nullable();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('commercial_businesses');
    }
};
