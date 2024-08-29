<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visitor', function (Blueprint $table) {

		$table->id();
		$table->string('ip');
		$table->unsignedBigInteger('user_id')->nullable();
		$table->unsignedBigInteger('visits')->default(1);
		$table->string('path')->nullable();
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('visitor');
    }
};
