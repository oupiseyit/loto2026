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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('bluetooth_enabled')->default(false);
            $table->enum('printer_size', ['58mm', '80mm'])->default('58mm');
            $table->enum('logo_mode', ['logo', 'text'])->default('logo');
            $table->enum('commission_mode', ['default', 'custom'])->default('default');
            $table->decimal('commission_value', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
