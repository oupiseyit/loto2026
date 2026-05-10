<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE currencies MODIFY COLUMN locale ENUM('en','km','vi','th') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE currencies SET locale = 'en' WHERE locale IS NULL OR locale NOT IN ('en','km','vi','th')");
        DB::statement("ALTER TABLE currencies MODIFY COLUMN locale ENUM('en','km','vi','th') NOT NULL");
    }
};
