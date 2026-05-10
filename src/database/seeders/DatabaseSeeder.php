<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed order matters — foreign-key dependencies resolved top-to-bottom:
     *
     *  1. AdminSeeder       — creates the admin user (id=1)
     *  2. UserSeeder        — creates master + staff users
     *  3. SettingSeeder     — default printer settings for every user
     *  4. ResultSeeder      — lottery draw results for past 14 days
     *  5. TicketSeeder      — tickets + bets for past 14 days (uses results for winner check)
     *  6. PayoutRecordSeeder— sample payout record rows from the image
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            CurrencySeeder::class,
            SettingSeeder::class,
            ResultSeeder::class,
            TicketSeeder::class,
            PayoutRecordSeeder::class,
            BetCategorySeeder::class,   
            BetTimeSettingSeeder::class,
        ]);
    }
}
