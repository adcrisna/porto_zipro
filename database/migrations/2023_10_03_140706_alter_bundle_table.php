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
        if (!Schema::hasColumn('product_bundle', 'name')) {
            Schema::table('product_bundle', function (Blueprint $table) {
                $table->string('name')->nullable();
            });
        }
        if (!Schema::hasColumn('product_bundle', 'deleted_at')) {
            Schema::table('product_bundle', function (Blueprint $table) {
                $table->softDeletes();
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
