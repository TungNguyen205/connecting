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
            $table->string('post_type')->nullable();
            $table->string('product_id')->nullable();
            $table->string('meta_link')->nullable();
            $table->string('sub_type')->nullable();
            $table->text('message')->nullable();
            $table->dateTime('time_on')->nullable();
            $table->string('social_id')->nullable();
            $table->bigInteger('pinterest_board_id')->nullable();
            $table->integer('shop_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('social_type')->nullable();
            $table->enum('status', ['publish', 'error'])->nullable();
            $table->string('post_social_id')->nullable();
            $table->string('error_message')->nullable();
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
        Schema::dropIfExists('post');
    }
}
