<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name' => 'USD',   'country_name' => 'English',  'symbol' => '$'],
            ['name' => 'KHR',   'country_name' => 'Cambodia', 'symbol' => '៛'],
            ['name' => 'đồng',  'country_name' => 'Vietnam',  'symbol' => '₫'],
            ['name' => 'baht',  'country_name' => 'Thailand', 'symbol' => '฿'],
        ];

        foreach ($currencies as $data) {
            Currency::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
