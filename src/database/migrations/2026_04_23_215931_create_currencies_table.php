<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->enum('locale', ['en', 'km', 'vi', 'th'])->unique();
            $table->string('name', 50);       // e.g. USD, KHR
            $table->string('symbol', 10);     // e.g. $, ៛
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
