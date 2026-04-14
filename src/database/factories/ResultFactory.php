<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Result>
 */
class ResultFactory extends Factory
{
    private const POSITIONS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    public function definition(): array
    {
        $session  = fake()->randomElement(['morning', 'noon', 'evening']);
        $position = fake()->randomElement(self::POSITIONS[$session]);

        return [
            'result_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'session'     => $session,
            'position'    => $position,
            'number'      => str_pad(fake()->numberBetween(0, 99), 2, '0', STR_PAD_LEFT),
            'entered_by'  => User::where('role', 'admin')->value('id') ?? 1,
        ];
    }

    /** Result for a specific date */
    public function onDate(string $date): static
    {
        return $this->state(['result_date' => $date]);
    }

    /** Result for a specific session */
    public function morning(): static
    {
        return $this->state([
            'session'  => 'morning',
            'position' => fake()->randomElement(self::POSITIONS['morning']),
        ]);
    }

    public function noon(): static
    {
        return $this->state([
            'session'  => 'noon',
            'position' => fake()->randomElement(self::POSITIONS['noon']),
        ]);
    }

    public function evening(): static
    {
        return $this->state([
            'session'  => 'evening',
            'position' => fake()->randomElement(self::POSITIONS['evening']),
        ]);
    }

    /** Force a specific winning number */
    public function number(string $number): static
    {
        return $this->state(['number' => str_pad($number, 2, '0', STR_PAD_LEFT)]);
    }
}
