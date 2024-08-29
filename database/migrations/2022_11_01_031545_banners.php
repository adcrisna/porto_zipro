<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {

            $table->id();
            $table->string('link')->nullable();
            $table->string('image')->nullable();
            $table->string('page_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isActive')->default(1);
            $table->boolean('is_internal')->default(0);
            $table->boolean('is_header')->default(0);
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
