<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bet_time_settings', function (Blueprint $table) {
            $table->id();
            $table->string('session_key', 50)->unique();
            $table->string('session_name', 100);
            $table->time('result_time');
            $table->json('group1_types');
            $table->time('group1_cutoff');
            $table->json('group2_types');
            $table->time('group2_cutoff');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bet_time_settings');
    }
};
