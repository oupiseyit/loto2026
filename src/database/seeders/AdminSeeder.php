<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'       => 'Admin',
                'email'      => 'admin@lotto.local',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'created_by' => null,
                'is_active'  => true,
            ]
        );
    }
}
