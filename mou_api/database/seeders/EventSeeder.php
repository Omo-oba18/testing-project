<?php

namespace Database\Seeders;

use App\Event;
use DB;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::whereNull('end_date')->update([
            'end_date' => DB::raw("DATE_FORMAT(start_date, '%Y-%m-%d 23:59:59')"),
        ]);
    }
}
