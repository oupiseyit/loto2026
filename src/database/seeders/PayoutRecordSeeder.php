<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayoutRecordSeeder extends Seeder
{
    /**
     * Seed payout_records table from the sample data image.
     *
     * game_code groups:
     *   "112"     — 3-digit game (2 rows)
     *   "1234"    — 4-digit game (2 rows)
     *   "11234"   — 5-digit game (4 rows)
     *   "111234"  — 6-digit game (13 rows) — main morning/evening sessions
     *   "1111234" — 7-digit game (5 rows)  — noon session (7 positions)
     *
     * Rows without letter/number/invoice/payout represent unused/empty slots
     * in the game card for that code group.
     */
    public function run(): void
    {
        $now = now();

        $records = [

            // ── 3-digit game "112" ───────────────────────
            ['game_code' => '112',     'bet_letter' => 'a', 'win_number' => '6',  'invoice_no' => '1123416',  'position_flag' => 'x', 'payout' => 135.00],
            ['game_code' => '113',     'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],

            // ── 4-digit game "1234" ──────────────────────
            ['game_code' => '1234',    'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '1234',    'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],

            // ── 5-digit game "11234" ─────────────────────
            ['game_code' => '11234',   'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '11234',   'bet_letter' => 'a', 'win_number' => '12', 'invoice_no' => '1234567',   'position_flag' => 'x', 'payout' => 210.00],
            ['game_code' => '11234',   'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '11234',   'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],

            // ── 6-digit game "111234" (13 rows) ─────────
            ['game_code' => '111234',  'bet_letter' => 'a', 'win_number' => '33', 'invoice_no' => '1234567',   'position_flag' => 'x', 'payout' => 90.00 ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => 'b', 'win_number' => '14', 'invoice_no' => '1234567',   'position_flag' => null,'payout' => 50.00 ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => 'a', 'win_number' => '33', 'invoice_no' => '2025014',   'position_flag' => 'x', 'payout' => 135.00],
            ['game_code' => '111234',  'bet_letter' => 'b', 'win_number' => '14', 'invoice_no' => '3355679',   'position_flag' => 'x', 'payout' => 135.00],
            ['game_code' => '111234',  'bet_letter' => 'b', 'win_number' => '14', 'invoice_no' => '3355679',   'position_flag' => 'x', 'payout' => 135.00],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '111234',  'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],

            // ── 7-digit game "1111234" (5 rows, noon) ───
            ['game_code' => '1111234', 'bet_letter' => 'a', 'win_number' => '20', 'invoice_no' => '33220514',  'position_flag' => null,'payout' => 151.00],
            ['game_code' => '1111234', 'bet_letter' => 'b', 'win_number' => '14', 'invoice_no' => '33220514',  'position_flag' => null,'payout' => null  ],
            ['game_code' => '1111234', 'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '1111234', 'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],
            ['game_code' => '1111234', 'bet_letter' => null,'win_number' => null, 'invoice_no' => null,        'position_flag' => null,'payout' => null  ],

        ];

        // Attach timestamps and insert
        $rows = array_map(fn ($r) => array_merge($r, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $records);

        DB::table('payout_records')->insert($rows);

        $this->command->info('PayoutRecordSeeder: inserted ' . count($rows) . ' rows.');
        $this->command->table(
            ['game_code', 'letter', 'win_number', 'invoice_no', 'flag', 'payout'],
            collect($records)
                ->filter(fn ($r) => $r['payout'] !== null)
                ->map(fn ($r) => [
                    $r['game_code'],
                    $r['bet_letter']  ?? '—',
                    $r['win_number']  ?? '—',
                    $r['invoice_no']  ?? '—',
                    $r['position_flag'] ?? '—',
                    $r['payout'],
                ])
                ->toArray()
        );
    }
}
