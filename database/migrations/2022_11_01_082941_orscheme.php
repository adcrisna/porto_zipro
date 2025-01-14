<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orscheme', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('upline_id')->unsigned()->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('orscheme');
    }
};
