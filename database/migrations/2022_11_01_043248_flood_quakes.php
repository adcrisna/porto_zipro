<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flood_quakes', function (Blueprint $table) {
            $table->id();
            $table->integer('plan')->nullable();
            $table->string('type');
            $table->string('default_rate');
            $table->integer('zone')->nullable();
            $table->mediumText('rate')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flood_quakes');
    }
};
