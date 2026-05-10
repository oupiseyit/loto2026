<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bet_time_settings', function (Blueprint $table) {
            $table->json('group_type')->nullable()->after('result_time');
        });
    }

    public function down(): void
    {
        Schema::table('bet_time_settings', function (Blueprint $table) {
            $table->dropColumn('group_type');
        });
    }
};
