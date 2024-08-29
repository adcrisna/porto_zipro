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
        Schema::create('schema_referral', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->longText('comission')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (!Schema::hasColumn('product', 'schema_ref_id')) {
            Schema::table('product', function (Blueprint $table) {
                $table->unsignedBigInteger('schema_ref_id')->nullable();
                $table->foreign('schema_ref_id')->references('id')->on('schema_referral');
            });
        }
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
