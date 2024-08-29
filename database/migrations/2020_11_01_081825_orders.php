<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
			$table->id();

			$table->bigInteger('user_id')->unsigned()->nullable();
			$table->bigInteger('transaction_id')->unsigned()->nullable();
			$table->unsignedbigInteger('product_id')->nullable();

			$table->unsignedbigInteger('cart_id')->nullable();
            $table->foreign('cart_id')->references('id')->on('cart');
			$table->longText('data')->nullable();

			$table->bigInteger('base_price');
			$table->bigInteger('total');

			$table->string('coupon')->nullable();
			$table->mediumText('additional_data')->nullable();
			$table->integer('status');
			$table->string('note')->nullable();
			$table->longText('validation')->nullable();

			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->boolean('is_offering')->default(0);
			$table->boolean('is_submit')->default(0);
			$table->string('offering_email')->nullable();
			$table->string('offering_telp')->nullable();
			$table->string('offering_name')->nullable();
			$table->timestamps();
			$table->softDeletes();
			
		});
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
