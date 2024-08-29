<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trx_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('trx_id')->references('id')->on('transaction');
            $table->foreign('user_id')->references('id')->on('users');

            $table->mediumText('formula')->nullable();
            $table->decimal('base_price',17,2);
            $table->decimal('comission',17,2);
            $table->string('status');
            $table->string('username')->nullable();
            $table->string('useremail')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('comission');
    }
};
