<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {

		$table->id();
		$table->bigInteger('category_id')->unsigned()->nullable();
        $table->foreign('category_id')->references('id')->on('category');
		$table->string('adira_product_id')->nullable();
		$table->bigInteger('binder_id')->default(129);

		$table->string('name');
		$table->mediumText('description');
		$table->bigInteger('price');
		$table->string('logo')->nullable();
		$table->mediumText('learn')->nullable();
		$table->string('flow');
		$table->longText('comission')->nullable();
		$table->longText('or_comission')->nullable();
		$table->string('display_name')->nullable();
		$table->bigInteger('period_days')->nullable();
		$table->longText('form_limit')->nullable();
		$table->longText('wording')->nullable();
		$table->string('additional_wording')->nullable();
		$table->longText('validation')->nullable();
		$table->integer('point')->default(0);
		$table->boolean('is_pg',1)->default(0);
		$table->boolean('is_enable')->default(0);
        $table->timestamps();
        $table->softDeletes();
        
    });
}

public function down()
    {
        Schema::dropIfExists('product');
    }
};
