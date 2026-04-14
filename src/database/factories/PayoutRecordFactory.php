<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayoutRecord>
 */
class PayoutRecordFactory extends Factory
{
    private const GAME_CODES  = ['112', '113', '1234', '11234', '111234', '1111234'];
    private const PAYOUT_RATES = [50, 90, 135, 151, 210];

    public function definition(): array
    {
        $hasBet = fake()->boolean(60); // 60% of rows have a bet

        return [
            'game_code'     => fake()->randomElement(self::GAME_CODES),
            'bet_letter'    => $hasBet ? fake()->randomElement(['a', 'b']) : null,
            'win_number'    => $hasBet ? str_pad(fake()->numberBetween(0, 99), 2, '0', STR_PAD_LEFT) : null,
            'invoice_no'    => $hasBet ? (string) fake()->numberBetween(1000000, 9999999) : null,
            'position_flag' => $hasBet ? fake()->randomElement(['x', null]) : null,
            'payout'        => $hasBet ? fake()->randomElement(self::PAYOUT_RATES) : null,
        ];
    }

    /** Row with all fields filled */
    public function filled(): static
    {
        return $this->state([
            'bet_letter'    => fake()->randomElement(['a', 'b']),
            'win_number'    => str_pad(fake()->numberBetween(0, 99), 2, '0', STR_PAD_LEFT),
            'invoice_no'    => (string) fake()->numberBetween(1000000, 9999999),
            'position_flag' => fake()->randomElement(['x', null]),
            'payout'        => fake()->randomElement(self::PAYOUT_RATES),
        ]);
    }

    /** X-type position (highest payout) */
    public function xType(): static
    {
        return $this->filled()->state([
            'position_flag' => 'x',
            'payout'        => fake()->randomElement([135, 210]),
        ]);
    }

    /** Standard position */
    public function standard(): static
    {
        return $this->filled()->state([
            'position_flag' => null,
            'payout'        => fake()->randomElement([50, 90]),
        ]);
    }

    /** Empty slot (no bet placed) */
    public function empty(): static
    {
        return $this->state([
            'bet_letter'    => null,
            'win_number'    => null,
            'invoice_no'    => null,
            'position_flag' => null,
            'payout'        => null,
        ]);
    }
}
