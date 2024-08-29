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
        if (!Schema::hasColumn('cart', 'is_offering')) {
            Schema::table('cart', function (Blueprint $table) {
                $table->boolean('is_offering')->default(0);
                $table->string('offering_email')->nullable();
                $table->string('offering_name')->nullable();
                $table->string('offering_telp')->nullable();
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
