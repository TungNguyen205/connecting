<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('content');
            $table->bigInteger('social_id')->unsigned();
            $table->tinyInteger('is_schedule')->default(0);
            $table->dateTime('schedule_time')->nullable();
            $table->tinyInteger('is_repeat')->default(0);
            $table->integer('repeat_value')->nullable();
            $table->string('repeat_unit')->nullable();
            $table->timestamps();

            $table->foreign('social_id')->references('id')->on('social')->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post');
    }
}
