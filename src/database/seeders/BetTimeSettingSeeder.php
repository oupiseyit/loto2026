<?php

namespace Database\Seeders;

use App\Models\BetTimeSetting;
use Illuminate\Database\Seeder;

class BetTimeSettingSeeder extends Seeder
{
    private const KH_POSTS = ['4K', 'A', 'B', 'C', 'D'];

    private const VN_DAY_POSTS = ['4P', '5P', '6P', '7P', 'Lo', 'LoF', 'LoFIN', 'A', 'B', 'C', 'D', 'F', 'I', 'N', 'K'];

    private const VN_NIGHT_POSTS = ['6P', '7P', 'Lo', 'A', 'B', 'C', 'D', 'A1', 'A2', 'A3', 'A4'];

    public function run(): void
    {
        // [lottery_type, session_name, result_time, posts] — from client mockup Sample/Feedback.jpg
        // ponytail: VN5D 18:30/19:35 assumed to be the យប់ (night) draws — confirm with client
        $draws = [
            ['KH5D',  'KH5D',        '10:35', self::KH_POSTS],
            ['KH5D',  'KH5D',        '13:00', self::KH_POSTS],
            ['KH5D',  'KH5D',        '15:35', self::KH_POSTS],
            ['KH5D',  'KH5D',        '18:00', self::KH_POSTS],
            ['KH5D',  'KH5D',        '19:45', self::KH_POSTS],
            ['VN5D',  'VN5D',        '10:35', self::VN_DAY_POSTS],
            ['VN5D',  'VN5D',        '13:00', self::VN_DAY_POSTS],
            ['VN5D',  'VN5D',        '16:30', self::VN_DAY_POSTS],
            ['VN5D',  'VN5D យប់',    '18:30', self::VN_NIGHT_POSTS],
            ['VN5D',  'VN5D យប់',    '19:35', self::VN_NIGHT_POSTS],
            ['VIP5D', 'VIP5D ល្ងាច', '16:30', self::VN_DAY_POSTS],
            ['VIP5D', 'VIP5D យប់',   '18:30', self::VN_NIGHT_POSTS],
        ];

        foreach ($draws as $i => [$type, $name, $time, $posts]) {
            $lo = array_values(array_intersect($posts, BetCategorySeeder::LO_POSTS));
            $p = array_values(array_diff($posts, BetCategorySeeder::LO_POSTS));
            $result = strtotime($time);

            BetTimeSetting::updateOrCreate(
                ['session_key' => strtolower($type).'_'.str_replace(':', '', $time)],
                [
                    'lottery_type' => $type,
                    'session_name' => $name,
                    'result_time' => $time.':00',
                    'group_type' => $posts,
                    // Lo posts close 20 min before the draw, the rest 10 min before
                    'group1_types' => $lo ?: $p,
                    'group1_cutoff' => date('H:i:s', $result - ($lo ? 1200 : 600)),
                    'group2_types' => $p,
                    'group2_cutoff' => date('H:i:s', $result - 600),
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]
            );
        }

        // Legacy morning/noon/afternoon/evening rows have no lottery type — hide them from betting
        BetTimeSetting::whereNull('lottery_type')->update(['is_active' => false]);
        BetTimeSetting::bustCache();
    }
}
