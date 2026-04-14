<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\Result;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    private const SESSIONS = ['morning', 'noon', 'evening'];

    private const LETTERS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    private const POSITIONS = ['X', 'W', 'H', 'W*'];
    private const BET_TYPES = ['ABCD', 'LO'];
    private const AMOUNTS   = [1000, 2000, 3000, 5000, 10000];

    public function run(): void
    {
        $staff = User::where('role', 'staff')->get();

        if ($staff->isEmpty()) {
            $this->command->warn('TicketSeeder: no staff users found — run UserSeeder first.');
            return;
        }

        // Pre-load all results keyed by "date|session|position" for winner calculation
        $results = Result::all()->keyBy(fn ($r) => "{$r->result_date}|{$r->session}|{$r->position}");

        $ticketCount = 0;
        $betCount    = 0;

        // Last 14 days (excluding today — today's results not entered yet)
        for ($daysAgo = 14; $daysAgo >= 1; $daysAgo--) {
            $date          = now()->subDays($daysAgo)->toDateString();
            $dateFormatted = str_replace('-', '', $date);

            foreach (self::SESSIONS as $session) {
                foreach ($staff as $user) {
                    // Each staff places 1–3 tickets per session per day
                    $numTickets = rand(1, 3);

                    for ($t = 0; $t < $numTickets; $t++) {
                        $invoiceNo = 'INV-' . $dateFormatted . '-' . strtoupper(Str::random(6));

                        $ticket = Ticket::create([
                            'user_id'        => $user->id,
                            'session'        => $session,
                            'bet_date'       => $date,
                            'total_amount'   => 0,
                            'invoice_number' => $invoiceNo,
                            'status'         => 'pending',
                            'win_amount'     => 0,
                        ]);

                        // 3–6 bets per ticket
                        $numBets     = rand(3, 6);
                        $totalAmount = 0;
                        $totalWin    = 0;
                        $letters     = self::LETTERS[$session];

                        for ($b = 0; $b < $numBets; $b++) {
                            $letter   = $letters[array_rand($letters)];
                            $position = self::POSITIONS[array_rand(self::POSITIONS)];
                            $number   = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
                            $amount   = self::AMOUNTS[array_rand(self::AMOUNTS)];

                            // Check if this bet wins against the seeded results
                            $resultKey   = "{$date}|{$session}|{$letter}";
                            $resultEntry = $results->get($resultKey);
                            $isWinner    = $resultEntry && $resultEntry->number === $number;
                            $winAmount   = $isWinner ? $amount * $this->payoutMultiplier($position) : 0;

                            Bet::create([
                                'ticket_id'  => $ticket->id,
                                'user_id'    => $user->id,
                                'bet_type'   => self::BET_TYPES[array_rand(self::BET_TYPES)],
                                'letter'     => $letter,
                                'position'   => $position,
                                'number'     => $number,
                                'amount'     => $amount,
                                'is_winner'  => $isWinner,
                                'win_amount' => $winAmount,
                            ]);

                            $totalAmount += $amount;
                            $totalWin    += $winAmount;
                            $betCount++;
                        }

                        // Update ticket totals and status
                        $ticket->update([
                            'total_amount' => $totalAmount,
                            'win_amount'   => $totalWin,
                            'status'       => $totalWin > 0 ? 'won' : 'lost',
                        ]);

                        $ticketCount++;
                    }
                }
            }
        }

        $this->command->info(
            "TicketSeeder: created {$ticketCount} tickets and {$betCount} bets " .
            "for {$staff->count()} staff over 14 days."
        );

        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Staff users',    $staff->count()],
                ['Days seeded',    14],
                ['Total tickets',  $ticketCount],
                ['Total bets',     $betCount],
                ['Avg bets/ticket', round($betCount / max(1, $ticketCount), 1)],
            ]
        );
    }

    /**
     * Payout multiplier per position type (in units of bet amount).
     * X = highest, W* = special, W/H = medium.
     */
    private function payoutMultiplier(string $position): float
    {
        return match ($position) {
            'X'  => 90,
            'W*' => 135,
            'W'  => 50,
            'H'  => 70,
            default => 90,
        };
    }
}
