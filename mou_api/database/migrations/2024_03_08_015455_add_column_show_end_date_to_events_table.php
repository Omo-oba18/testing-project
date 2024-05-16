<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('show_end_date')->after('end_date')->default(true);
        });

        $events = DB::table('events')->whereNotNull('end_date')->get();
        foreach ($events as $key => $event) {
            DB::table('events')
                ->where('id', $event->id)
                ->update([
                    'show_end_date' => (date('Y-m-d', strtotime($event->start_date)) == date('Y-m-d', strtotime($event->end_date)) && date('H:i:s', strtotime($event->end_date)) == '23:59:59') ? false : true,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('show_end_date');
        });
    }
};
