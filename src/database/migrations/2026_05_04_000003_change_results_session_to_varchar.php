<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('session', 50)->change();
            $table->index('session', 'results_session_idx');
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('results_session_idx');
            $table->enum('session', ['morning', 'noon', 'evening'])->change();
        });
    }
};
