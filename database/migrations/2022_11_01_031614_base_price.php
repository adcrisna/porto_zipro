<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('base_price', function (Blueprint $table) {

            $table->id();
            $table->string('code')->nullable();
            $table->string('brand')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('typeDetail')->nullable();
            $table->string('modelName')->nullable();
            $table->string('modelCode')->nullable();
            $table->mediumText('tahun')->nullable();
            $table->mediumText('price')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('base_price');
    }
};
