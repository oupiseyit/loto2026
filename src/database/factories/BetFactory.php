<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bet>
 */
class BetFactory extends Factory
{
    // Letters available per session
    private const LETTERS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    private const POSITIONS  = ['X', 'W', 'H', 'W*'];
    private const BET_TYPES  = ['ABCD', 'LO'];
    private const AMOUNTS    = [1000, 2000, 3000, 5000, 10000];

    public function definition(): array
    {
        $ticket = Ticket::first() ?? Ticket::factory()->create();
        $letter = fake()->randomElement(self::LETTERS[$ticket->session] ?? self::LETTERS['morning']);

        return [
            'ticket_id'  => $ticket->id,
            'user_id'    => $ticket->user_id,
            'bet_type'   => fake()->randomElement(self::BET_TYPES),
            'letter'     => $letter,
            'position'   => fake()->randomElement(self::POSITIONS),
            'number'     => str_pad(fake()->numberBetween(0, 99), 2, '0', STR_PAD_LEFT),
            'amount'     => fake()->randomElement(self::AMOUNTS),
            'is_winner'  => false,
            'win_amount' => 0,
        ];
    }

    /** Attach bet to a specific ticket (uses ticket session for valid letters) */
    public function forTicket(Ticket $ticket): static
    {
        $letter = fake()->randomElement(self::LETTERS[$ticket->session] ?? self::LETTERS['morning']);

        return $this->state([
            'ticket_id' => $ticket->id,
            'user_id'   => $ticket->user_id,
            'letter'    => $letter,
        ]);
    }

    /** Mark as a winning bet */
    public function winner(float $multiplier = 90): static
    {
        return $this->state(function (array $attributes) use ($multiplier) {
            return [
                'is_winner'  => true,
                'win_amount' => $attributes['amount'] * $multiplier,
            ];
        });
    }

    /** ABCD bet type */
    public function abcd(): static { return $this->state(['bet_type' => 'ABCD']); }

    /** LO bet type */
    public function lo(): static { return $this->state(['bet_type' => 'LO']); }

    /** X-position bet */
    public function positionX(): static { return $this->state(['position' => 'X']); }

    /** Force a specific number */
    public function number(string $number): static
    {
        return $this->state(['number' => str_pad($number, 2, '0', STR_PAD_LEFT)]);
    }
}
