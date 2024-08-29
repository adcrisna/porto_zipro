<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            // $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('order_id')->references('id')->on('order');
            $table->foreign('cart_id')->references('id')->on('cart');
            $table->mediumText('trx_data')->nullable();

            $table->decimal('base_price',17,2);
            $table->decimal('deduct_price',17,2);
            $table->decimal('total',17,2);
            $table->string('voucher')->nullable();
            $table->mediumText('policy')->nullable();
            $table->datetime('expiry_date')->nullable();
            $table->date('policy_start')->nullable();
            $table->date('policy_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction');
    }
};
