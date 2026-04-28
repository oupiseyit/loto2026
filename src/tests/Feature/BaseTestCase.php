<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $master;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin  = User::factory()->admin()->create();
        $this->master = User::factory()->master()->create(['created_by' => $this->admin->id]);
        $this->staff  = User::factory()->staff()->create(['created_by'  => $this->master->id]);
    }

    protected function actingAsAdmin(): static  { return $this->actingAs($this->admin); }
    protected function actingAsMaster(): static { return $this->actingAs($this->master); }
    protected function actingAsStaff(): static  { return $this->actingAs($this->staff); }

    protected function apiToken(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function apiAs(User $user): static
    {
        return $this->withToken($this->apiToken($user));
    }
}
