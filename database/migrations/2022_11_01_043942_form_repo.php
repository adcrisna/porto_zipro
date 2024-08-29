<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('form_repo', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->mediumText('lang')->nullable();
            $table->string('form_type')->nullable();
            $table->integer('value')->unsigned()->nullable();
            $table->string('validate_link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_repo');
    }
};
