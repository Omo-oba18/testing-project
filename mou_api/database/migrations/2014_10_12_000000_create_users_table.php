<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('phone_number', 20)->unique();
            $table->string('dial_code', 4);

            $table->string('country_code', 2)->nullable();
            $table->string('city')->nullable();

            $table->string('avatar')->nullable();
            $table->date('birthday')->nullable();
            $table->tinyInteger('gender')->nullable()->comment('0 - Woman| 1 - Man');

            $table->string('facebook_id', 50)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
