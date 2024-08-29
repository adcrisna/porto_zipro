<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profile', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->unsignedBigInteger("bank_id")->nullable();
            $table->foreign("user_id")->references("id")->on("users")->onUpdate('cascade')->onDelete("cascade");
            $table->foreign("bank_id")->references("id")->on("bank")->onUpdate("cascade")->onDelete("cascade");

            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('another_phone')->nullable();
            $table->string('city')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('id_card_pic')->nullable();
            $table->string('avatar')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('branch_location')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('profile');
    }
};
