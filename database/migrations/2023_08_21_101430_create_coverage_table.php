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
        Schema::create('travel_coverages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('adira_product_id')->nullable();
            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('id_coverage')->nullable();
            $table->string('package_type')->nullable();
            $table->string('product_type')->nullable();
            $table->string('name')->nullable();
            $table->mediumText('description')->nullable();

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
        Schema::dropIfExists('coverage');
    }
};
