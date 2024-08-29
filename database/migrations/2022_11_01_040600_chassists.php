<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chassists', function (Blueprint $table) {

            $table->id();
            $table->string('chassis')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('chassists');
    }
};
