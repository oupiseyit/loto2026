<?php

namespace Database\Seeders;

use App\Models\BetCategory;
use Illuminate\Database\Seeder;

class BetCategorySeeder extends Seeder
{
    /** Posts (ប៉ុស្តិ៍) used by KH5D / VN5D / VIP5D. Lo* posts close earlier (group1 cutoff). */
    public const P_POSTS = ['4K', '4P', '5P', '6P', '7P', 'A', 'B', 'C', 'D', 'F', 'I', 'N', 'K', 'A1', 'A2', 'A3', 'A4'];

    public const LO_POSTS = ['Lo', 'LoF', 'LoFIN'];

    public function run(): void
    {
        $sort = 1;
        foreach ([BetCategory::TYPE_P => self::P_POSTS, BetCategory::TYPE_LO => self::LO_POSTS] as $type => $names) {
            foreach ($names as $name) {
                BetCategory::firstOrCreate(
                    ['name' => $name, 'type' => $type],
                    ['status' => true, 'sort_order' => $sort]
                );
                $sort++;
            }
        }
    }
}
