<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('draft','pending','won','lost') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tickets SET status = 'pending' WHERE status = 'draft'");
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('pending','won','lost') DEFAULT 'pending'");
    }
};
