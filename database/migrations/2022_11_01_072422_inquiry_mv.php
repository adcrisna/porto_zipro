<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inquiry_mv', function (Blueprint $table) {

		$table->id();
		$table->mediumText('item')->nullable();
		$table->double('total',11,2)->nullable();

		$table->unsignedBigInteger('product_id')->nullable();
		$table->unsignedBigInteger('order_id')->nullable();
        $table->foreign('product_id')->references('id')->on('product');
        $table->foreign('order_id')->references('id')->on('orders');

		$table->boolean('status')->default(0);
		$table->mediumText('data')->nullable();
		$table->double('discount', 9,6)->nullable();
		$table->string('offering_email')->nullable();
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('inquiry_mv');
    }
};
