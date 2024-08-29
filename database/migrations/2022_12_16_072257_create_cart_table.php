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
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name')->nullable();
            $table->bigInteger('total')->default(0)->nullable();
            $table->longText('data')->nullable();
            $table->boolean('is_checkout')->default(0);
            $table->double('admin_fee', 18 , 2)->nullable();
            $table->string('pg_method')->nullable();
            $table->string('pg_status')->nullable();
            $table->longText('pg_callback')->nullable();
            $table->text('pg_link')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart');
    }
};
