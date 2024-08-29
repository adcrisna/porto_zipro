<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {

            $table->id();
            $table->string('code',10)->nullable();
            $table->text('ceiling');
            $table->text('floor');
            $table->string('type')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('zones');
    }
};
