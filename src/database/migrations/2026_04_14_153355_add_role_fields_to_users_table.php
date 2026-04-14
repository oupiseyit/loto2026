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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('name');
            $table->enum('role', ['admin', 'master', 'staff'])->default('staff')->after('username');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('role');
            $table->boolean('is_active')->default(true)->after('created_by');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['username', 'role', 'created_by', 'is_active']);
        });
    }
};
