<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test', function (Blueprint $table) {

            $table->id();
            $table->float('float',13,10);
            $table->double('double', 10, 5);
            $table->decimal('decimal',15,10);

        });
    }

    public function down()
    {
        Schema::dropIfExists('test');
    }
};
