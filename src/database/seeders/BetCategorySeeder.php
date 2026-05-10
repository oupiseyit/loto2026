<?php

namespace Database\Seeders;

use App\Models\BetCategory;
use Illuminate\Database\Seeder;

class BetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // P type (type=1)
            ['name' => 'P1',   'type' => BetCategory::TYPE_P,  'sort_order' => 1],
            ['name' => 'P2',   'type' => BetCategory::TYPE_P,  'sort_order' => 2],
            ['name' => 'P3',   'type' => BetCategory::TYPE_P,  'sort_order' => 3],
            ['name' => 'P4',   'type' => BetCategory::TYPE_P,  'sort_order' => 4],
            ['name' => 'P5',   'type' => BetCategory::TYPE_P,  'sort_order' => 5],
            ['name' => 'P6',   'type' => BetCategory::TYPE_P,  'sort_order' => 6],
            ['name' => 'P7',   'type' => BetCategory::TYPE_P,  'sort_order' => 7],
            ['name' => 'P8',   'type' => BetCategory::TYPE_P,  'sort_order' => 8],
            // LO type (type=2)
            ['name' => 'Lo23', 'type' => BetCategory::TYPE_LO, 'sort_order' => 9],
            ['name' => 'Lo25', 'type' => BetCategory::TYPE_LO, 'sort_order' => 10],
            ['name' => 'Lo27', 'type' => BetCategory::TYPE_LO, 'sort_order' => 11],
        ];

        foreach ($categories as $data) {
            BetCategory::firstOrCreate(
                ['name' => $data['name'], 'type' => $data['type']],
                ['status' => true, 'sort_order' => $data['sort_order']]
            );
        }
    }
}
