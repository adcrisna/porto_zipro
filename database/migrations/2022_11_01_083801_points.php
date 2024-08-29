<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('points', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('transaction_id')->references('id')->on('transaction');

            $table->integer('point')->default(0);
            $table->integer('gwp')->default(0);
            $table->boolean('eligible')->default(0);
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('points');
    }
};
