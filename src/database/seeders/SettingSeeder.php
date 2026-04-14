<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Create default settings for every user in the database.
     * Uses firstOrCreate — safe to run multiple times.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Setting::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bluetooth_enabled' => false,
                    'printer_size'      => '58mm',
                    'logo_mode'         => 'logo',
                    'commission_mode'   => 'default',
                    'commission_value'  => null,
                ]
            );
        }

        $this->command->info("SettingSeeder: ensured settings for {$users->count()} users.");
    }
}
