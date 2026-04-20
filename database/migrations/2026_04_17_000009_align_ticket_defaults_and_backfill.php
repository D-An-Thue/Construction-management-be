<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('Tickets')->where('Status', 0)->update(['Status' => 1]);
        DB::table('Tickets')->where('TicketType', 0)->update(['TicketType' => 1]);
    }

    public function down(): void
    {
    }
};
