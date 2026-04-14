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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->date('result_date');
            $table->enum('session', ['morning', 'noon', 'evening']);
            $table->string('position', 5);
            $table->string('number', 10);
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['result_date', 'session', 'position']);
            $table->index('result_date');
            $table->index('session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
