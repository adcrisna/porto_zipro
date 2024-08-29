<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partners_product', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('partner_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('product');
            $table->foreign('partner_id')->references('id')->on('partners');
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('partners_product');
    }
};
