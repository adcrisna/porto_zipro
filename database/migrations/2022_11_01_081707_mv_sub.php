<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mv_sub', function (Blueprint $table) {

            $table->id();
            $table->string('name');
            $table->string('code');
            $table->boolean('zoning');
            $table->boolean('plan')->nullable();
            $table->boolean('mv');
            $table->boolean('moto');
            $table->string('against');
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('mv_sub');
    }
};
