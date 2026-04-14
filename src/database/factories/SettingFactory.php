<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        $commissionMode = fake()->randomElement(['default', 'custom']);

        return [
            'user_id'           => User::factory(),
            'bluetooth_enabled' => fake()->boolean(30),
            'printer_size'      => fake()->randomElement(['58mm', '80mm']),
            'logo_mode'         => fake()->randomElement(['logo', 'text']),
            'commission_mode'   => $commissionMode,
            'commission_value'  => $commissionMode === 'custom'
                ? fake()->randomFloat(2, 1, 20)
                : null,
        ];
    }

    /** Default settings (no customisation) */
    public function defaults(): static
    {
        return $this->state([
            'bluetooth_enabled' => false,
            'printer_size'      => '58mm',
            'logo_mode'         => 'logo',
            'commission_mode'   => 'default',
            'commission_value'  => null,
        ]);
    }

    /** Bluetooth enabled */
    public function bluetoothOn(): static
    {
        return $this->state(['bluetooth_enabled' => true]);
    }

    /** Custom commission */
    public function customCommission(float $value = 5.00): static
    {
        return $this->state([
            'commission_mode'  => 'custom',
            'commission_value' => $value,
        ]);
    }

    /** For a specific user */
    public function forUser(int $userId): static
    {
        return $this->state(['user_id' => $userId]);
    }
}
