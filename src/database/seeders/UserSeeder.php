<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- Master users (created_by = admin id 1) ---
        $master1 = User::firstOrCreate(
            ['username' => 'master1'],
            [
                'name'       => 'Master One',
                'email'      => 'master1@lotto.local',
                'password'   => Hash::make('master123'),
                'role'       => 'master',
                'created_by' => 1,   // admin
                'is_active'  => true,
            ]
        );

        $master2 = User::firstOrCreate(
            ['username' => 'master2'],
            [
                'name'       => 'Master Two',
                'email'      => 'master2@lotto.local',
                'password'   => Hash::make('master123'),
                'role'       => 'master',
                'created_by' => 1,
                'is_active'  => true,
            ]
        );

        // --- Staff under master1 ---
        User::firstOrCreate(
            ['username' => 'staff1'],
            [
                'name'       => 'Staff One',
                'email'      => 'staff1@lotto.local',
                'password'   => Hash::make('staff123'),
                'role'       => 'staff',
                'created_by' => $master1->id,
                'is_active'  => true,
            ]
        );

        User::firstOrCreate(
            ['username' => 'staff2'],
            [
                'name'       => 'Staff Two',
                'email'      => 'staff2@lotto.local',
                'password'   => Hash::make('staff123'),
                'role'       => 'staff',
                'created_by' => $master1->id,
                'is_active'  => true,
            ]
        );

        // --- Staff under master2 ---
        User::firstOrCreate(
            ['username' => 'staff3'],
            [
                'name'       => 'Staff Three',
                'email'      => 'staff3@lotto.local',
                'password'   => Hash::make('staff123'),
                'role'       => 'staff',
                'created_by' => $master2->id,
                'is_active'  => true,
            ]
        );

        $this->command->info('Users seeded:');
        $this->command->table(
            ['Username', 'Role', 'Password'],
            [
                ['master1', 'master', 'master123'],
                ['master2', 'master', 'master123'],
                ['staff1',  'staff',  'staff123  (under master1)'],
                ['staff2',  'staff',  'staff123  (under master1)'],
                ['staff3',  'staff',  'staff123  (under master2)'],
            ]
        );
    }
}
