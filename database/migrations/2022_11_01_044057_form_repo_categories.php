<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('form_repo_categories', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('form_repo_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('form_repo_id')->references('id')->on('form_repo');
            $table->foreign('category_id')->references('id')->on('category');

            $table->mediumText('form_json')->nullable();
            $table->mediumText('form_confirm')->nullable();
            $table->mediumText('form_validation')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('form_repo_categories');
    }
};
