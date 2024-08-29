<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kode_plat', function (Blueprint $table) {
            $table->id();
            $table->string('wilayah')->nullable();
            $table->string('kode')->nullable();
            $table->string('daerah')->nullable();
            $table->mediumText('kabupaten')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kode_plat');
    }
};
