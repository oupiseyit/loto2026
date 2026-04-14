<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    private static array $SESSIONS = ['morning', 'noon', 'evening'];

    public function definition(): array
    {
        $date    = fake()->dateTimeBetween('-30 days', 'now');
        $session = fake()->randomElement(self::$SESSIONS);

        return [
            'user_id'        => User::factory()->staff(),
            'session'        => $session,
            'bet_date'       => $date->format('Y-m-d'),
            'total_amount'   => 0,   // recalculated after bets are attached
            'invoice_number' => 'INV-' . $date->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'status'         => 'pending',
            'win_amount'     => 0,
        ];
    }

    /** Force a specific session */
    public function morning(): static  { return $this->state(['session' => 'morning']); }
    public function noon(): static     { return $this->state(['session' => 'noon']); }
    public function evening(): static  { return $this->state(['session' => 'evening']); }

    /** Mark ticket as won */
    public function won(float $winAmount = 90000): static
    {
        return $this->state([
            'status'     => 'won',
            'win_amount' => $winAmount,
        ]);
    }

    /** Mark ticket as lost */
    public function lost(): static
    {
        return $this->state(['status' => 'lost', 'win_amount' => 0]);
    }

    /** Set a specific user */
    public function forUser(int $userId): static
    {
        return $this->state(['user_id' => $userId]);
    }

    /** Set a specific date */
    public function onDate(string $date): static
    {
        return $this->state([
            'bet_date'       => $date,
            'invoice_number' => 'INV-' . str_replace('-', '', $date) . '-' . strtoupper(Str::random(6)),
        ]);
    }
}
