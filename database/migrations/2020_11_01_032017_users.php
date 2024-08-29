<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

		$table->id();
		$table->bigInteger('partner_id')->unsigned()->nullable();
		$table->string('uuid')->nullable();
		$table->string('name')->nullable();
		$table->string('email');
		$table->string('password');
		$table->string('remember_token')->nullable();
		$table->timestamp('verified_at')->nullable();
		$table->string('referrer_email')->nullable();
		$table->timestamp('cooldown')->nullable();
		$table->double('tax',9,6)->default('2.500000');
		$table->timestamp('email_verified_at')->nullable();
		$table->string('fcm_token')->nullable();
		$table->text('oauth_token')->nullable();
		$table->timestamps();
        $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
