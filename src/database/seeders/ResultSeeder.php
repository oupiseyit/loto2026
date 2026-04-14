<?php

namespace Database\Seeders;

use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    private const POSITIONS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    /**
     * Seed lottery results for the past 14 days (all sessions, all positions).
     * Skips today (results not yet entered) and uses firstOrCreate so it's idempotent.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $count = 0;

        // Day 14 back → day 1 back (not today)
        for ($daysAgo = 14; $daysAgo >= 1; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();

            foreach (self::POSITIONS as $session => $positions) {
                foreach ($positions as $position) {
                    Result::firstOrCreate(
                        [
                            'result_date' => $date,
                            'session'     => $session,
                            'position'    => $position,
                        ],
                        [
                            'number'     => str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT),
                            'entered_by' => $admin->id,
                        ]
                    );
                    $count++;
                }
            }
        }

        // Total expected: 14 days × (4 + 7 + 4) positions = 14 × 15 = 210
        $this->command->info("ResultSeeder: created/verified {$count} result entries (14 days × 15 positions).");
    }
}
