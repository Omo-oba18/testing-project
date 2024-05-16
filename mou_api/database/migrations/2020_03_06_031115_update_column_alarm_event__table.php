<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnAlarmEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('alarm', 50)->nullable()->change();
            $table->string('repeat', 15)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {

            $table->unsignedTinyInteger('repeat')->nullable()->comment('1 (for Monday) through 7 (for Sunday)')->change();
            $table->string('alarm', 5)->nullable()->comment('5m, 10m, 30m, 1h, 1d, 1w')->change();

        });
    }
}
