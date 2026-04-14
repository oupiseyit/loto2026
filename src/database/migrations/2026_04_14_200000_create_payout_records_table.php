<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payout records table — stores lottery winning payout entries.
     *
     * Columns (matching the sample data image):
     *   game_code    — lottery game type code (e.g. "112", "1234", "11234", "111234", "1111234")
     *   bet_letter   — position letter: 'a' (→ A) or 'b' (→ B) — null if slot unused
     *   win_number   — the lottery number that was bet/won (e.g. 06, 12, 33)
     *   invoice_no   — ticket invoice reference number
     *   position_flag— position type flag: 'x' (X-type bet) or null (standard)
     *   payout       — payout amount in KHR thousands; null if not a winning row
     */
    public function up(): void
    {
        Schema::create('payout_records', function (Blueprint $table) {
            $table->id();
            $table->string('game_code', 20)->index();      // "112", "1234", "11234", "111234", "1111234"
            $table->char('bet_letter', 1)->nullable();     // 'a' or 'b'
            $table->string('win_number', 10)->nullable();  // "6", "12", "33", "14", "20"
            $table->string('invoice_no', 20)->nullable();  // invoice reference
            $table->char('position_flag', 1)->nullable();  // 'x' = X-type, null = standard
            $table->decimal('payout', 10, 2)->nullable();  // payout in KHR thousands
            $table->timestamps();

            $table->index(['game_code', 'bet_letter']);
            $table->index('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_records');
    }
};
