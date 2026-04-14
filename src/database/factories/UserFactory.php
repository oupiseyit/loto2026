<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'       => fake()->name(),
            'username'   => fake()->unique()->userName(),
            'email'      => fake()->unique()->safeEmail(),
            'password'   => static::$password ??= Hash::make('password'),
            'role'       => 'staff',
            'created_by' => null,
            'is_active'  => true,
            'remember_token' => Str::random(10),
        ];
    }

    /** Admin state */
    public function admin(): static
    {
        return $this->state([
            'role'       => 'admin',
            'created_by' => null,
        ]);
    }

    /** Master state */
    public function master(?int $adminId = null): static
    {
        return $this->state([
            'role'       => 'master',
            'created_by' => $adminId ?? 1,
        ]);
    }

    /** Staff state — optionally linked to a master */
    public function staff(?int $masterId = null): static
    {
        return $this->state([
            'role'       => 'staff',
            'created_by' => $masterId ?? 1,
        ]);
    }

    /** Inactive account */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** Unverified email */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
