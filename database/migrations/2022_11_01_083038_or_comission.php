<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('or_comission', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('trx_id');
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('to_id');
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->longText('formula')->nullable();
            $table->integer('layer');
            $table->double('base_price', 17,2);
            $table->double('comission', 17,2);
            $table->string('status');
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('or_comission');
    }
};
