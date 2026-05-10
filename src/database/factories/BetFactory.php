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
    private const LETTERS   = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'Lo23', 'Lo25', 'Lo27'];
    private const POSITIONS = ['X', 'W', 'H', 'W*'];
    private const BET_TYPES = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'Lo23', 'Lo25', 'Lo27'];
    private const AMOUNTS    = [1000, 2000, 3000, 5000, 10000];

    public function definition(): array
    {
        $ticket = Ticket::first() ?? Ticket::factory()->create();
        $letter = fake()->randomElement(self::LETTERS);

        return [
            'ticket_id'  => $ticket->id,
            'user_id'    => $ticket->user_id,
            'bet_type'   => $letter,
            'letter'     => $letter,
            'position'   => fake()->randomElement(self::POSITIONS),
            'number'     => str_pad(fake()->numberBetween(0, 99), 2, '0', STR_PAD_LEFT),
            'amount'     => fake()->randomElement(self::AMOUNTS),
            'is_winner'  => false,
            'win_amount' => 0,
        ];
    }

    /** Attach bet to a specific ticket */
    public function forTicket(Ticket $ticket): static
    {
        $letter = fake()->randomElement(self::LETTERS);

        return $this->state([
            'ticket_id' => $ticket->id,
            'user_id'   => $ticket->user_id,
            'bet_type'  => $letter,
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

    /** P-type bet */
    public function p(string $name = 'P1'): static { return $this->state(['bet_type' => $name, 'letter' => $name]); }

    /** LO-type bet */
    public function lo(string $name = 'Lo27'): static { return $this->state(['bet_type' => $name, 'letter' => $name]); }

    /** X-position bet */
    public function positionX(): static { return $this->state(['position' => 'X']); }

    /** Force a specific number */
    public function number(string $number): static
    {
        return $this->state(['number' => str_pad($number, 2, '0', STR_PAD_LEFT)]);
    }
}
