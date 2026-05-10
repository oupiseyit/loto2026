<?php

namespace Database\Seeders;

use App\Models\BetCategory;
use App\Models\BetTimeSetting;
use Illuminate\Database\Seeder;

class BetTimeSettingSeeder extends Seeder
{
    public function run(): void
    {
        $pNames = BetCategory::where('type', BetCategory::TYPE_P)->orderBy('sort_order')->pluck('name')->all();
        $loNames = BetCategory::where('type', BetCategory::TYPE_LO)->orderBy('sort_order')->pluck('name')->all();
        $allNames = array_merge($loNames, $pNames);

        $sessions = [
            [
                'session_key' => 'morning',
                'session_name' => 'Morning',
                'result_time' => '10:30:00',
                'group_type' => $allNames,
                'group1_types' => $loNames,
                'group1_cutoff' => '10:10:00',
                'group2_types' => $pNames,
                'group2_cutoff' => '10:20:00',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'session_key' => 'noon',
                'session_name' => 'Noon',
                'result_time' => '13:30:00',
                'group_type' => $allNames,
                'group1_types' => $loNames,
                'group1_cutoff' => '13:10:00',
                'group2_types' => $pNames,
                'group2_cutoff' => '13:20:00',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'session_key' => 'afternoon',
                'session_name' => 'Afternoon',
                'result_time' => '16:30:00',
                'group_type' => $allNames,
                'group1_types' => $loNames,
                'group1_cutoff' => '16:10:00',
                'group2_types' => $pNames,
                'group2_cutoff' => '16:20:00',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'session_key' => 'evening',
                'session_name' => 'Evening',
                'result_time' => '18:30:00',
                'group_type' => $loNames,
                'group1_types' => $loNames,
                'group1_cutoff' => '18:10:00',
                'group2_types' => $pNames,
                'group2_cutoff' => '18:20:00',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($sessions as $data) {
            BetTimeSetting::updateOrCreate(
                ['session_key' => $data['session_key']],
                $data
            );
        }
    }
}
