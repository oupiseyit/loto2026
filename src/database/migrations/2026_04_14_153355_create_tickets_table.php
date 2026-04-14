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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('session', ['morning', 'noon', 'evening']);
            $table->date('bet_date');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('invoice_number', 30)->unique();
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->decimal('win_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'bet_date']);
            $table->index('session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
