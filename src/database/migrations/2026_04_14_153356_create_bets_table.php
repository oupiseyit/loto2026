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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('bet_type', ['ABCD', 'LO']);
            $table->string('letter', 10);
            $table->string('position', 5);
            $table->string('number', 10);
            $table->decimal('amount', 10, 2);
            $table->boolean('is_winner')->default(false);
            $table->decimal('win_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('number');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
