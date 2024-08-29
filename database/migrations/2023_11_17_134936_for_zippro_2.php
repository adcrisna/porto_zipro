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
        if (!Schema::hasColumn('product', 'momentic_2_prod')) {
            Schema::table('product', function (Blueprint $table) {
                $table->bigInteger('momentic_2_prod')->nullable();
                $table->double('bf', 9, 6)->nullable();
                $table->double('discount', 9, 6)->nullable();
                $table->string('project_product_id')->nullable();
                $table->string('product_key')->nullable();
                $table->string('project_key')->nullable();
            });
        }

        if (!Schema::hasColumn('transaction', 'contract_id')) {
            Schema::table('transaction', function (Blueprint $table) {
                $table->string('contract_id')->nullable();
                $table->string('momentic_log')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'version')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('version')->nullable();
                $table->boolean('is_production')->nullable();
                $table->string('platform')->nullable();
                $table->timestamp('last_login')->nullable();
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
