<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('platform_id');
            $table->string('title', 1000);
            $table->string('handle', 1000)->nullable();
            $table->string('image', 2000)->nullable();
            $table->text('images')->nullable();
            $table->bigInteger('shop_id')->unsigned();
            $table->string('price');
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shop')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product');
    }
}
