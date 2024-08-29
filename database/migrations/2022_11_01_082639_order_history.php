<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_histories', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->string('status')->nullable();
            $table->integer('user_id')->nullable();
            $table->mediumText('data')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('order_histories');
    }
};
