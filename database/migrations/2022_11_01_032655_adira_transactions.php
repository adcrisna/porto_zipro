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
        Schema::create('adira_transactions', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->string('adira_status')->nullable();
            $table->longText('adira_response')->nullable();
            $table->longText('polling_response')->nullable();
            $table->longText('resubmit_response')->nullable();
            $table->longText('postmikro_response')->nullable();
            $table->longText('post_finish')->nullable();
            $table->longText('document_policy')->nullable();
            $table->longText('cover_note')->nullable();
            $table->string('status')->nullable();
            $table->longText('log_api')->nullable();
            $table->string('request_number')->nullable();
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
        //
    }
};
