<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppColumnDeviceFcmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_device_fcms', function (Blueprint $table) {
            $table->char('app')->default(\App\UserDeviceFcm::PERSONAL_APP);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_device_fcms', function (Blueprint $table) {
            $table->dropColumn('app');
        });
    }
}
