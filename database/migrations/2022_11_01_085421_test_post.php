<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_post', function (Blueprint $table) {

            $table->id();
            $table->mediumText('value')->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('test_post');
    }
};
