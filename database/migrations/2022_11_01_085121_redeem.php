<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('redeem', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('no_hp')->nullable();
            $table->string('e_wallet')->nullable();
            $table->integer('redeem_point')->default('0');
            $table->string('status')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('redeem');
    }
};
