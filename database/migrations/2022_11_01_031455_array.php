<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('array', function (Blueprint $table) {

            $table->id();
            $table->mediumText('value')->nullable();
            $table->string('name')->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('array');
    }
};
