<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE bets MODIFY bet_type VARCHAR(10) NOT NULL');
        DB::statement('UPDATE bets SET bet_type = letter');
    }

    public function down(): void
    {
        DB::statement("UPDATE bets SET bet_type = CASE WHEN bet_type LIKE 'Lo%' THEN 'LO' ELSE 'ABCD' END");
        DB::statement("ALTER TABLE bets MODIFY bet_type ENUM('ABCD', 'LO') NOT NULL");
    }
};
