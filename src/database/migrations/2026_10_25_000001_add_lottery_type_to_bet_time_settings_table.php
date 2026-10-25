<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bet_time_settings', function (Blueprint $table) {
            // KH5D / VN5D / VIP5D — each row is one draw time of one lottery type
            $table->string('lottery_type', 20)->nullable()->after('session_key');
        });
    }

    public function down(): void
    {
        Schema::table('bet_time_settings', function (Blueprint $table) {
            $table->dropColumn('lottery_type');
        });
    }
};
