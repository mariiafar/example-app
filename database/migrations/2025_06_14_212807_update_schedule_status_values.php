<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('schedules')
            ->where('status', 'Свободно')
            ->update(['status' => 'available']);

        DB::table('schedules')
            ->where('status', 'Занято')
            ->update(['status' => 'busy']);

        DB::table('schedules')
            ->where('status', 'Отменено')
            ->update(['status' => 'canceled']);
    }

    public function down(): void
    {
        DB::table('schedules')
            ->where('status', 'available')
            ->update(['status' => 'Свободно']);

        DB::table('schedules')
            ->where('status', 'busy')
            ->update(['status' => 'Занято']);

        DB::table('schedules')
            ->where('status', 'canceled')
            ->update(['status' => 'Отменено']);
    }
};