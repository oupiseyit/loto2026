<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BetTimeSetting extends Model
{
    protected $fillable = [
        'session_key',
        'session_name',
        'result_time',
        'group_type',
        'group1_types',
        'group1_cutoff',
        'group2_types',
        'group2_cutoff',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'group_type' => 'array',
        'group1_types' => 'array',
        'group2_types' => 'array',
        'is_active' => 'boolean',
    ];

    public static function active(): Collection
    {
        return Cache::remember('bet_time_settings.active', 300, fn (): Collection => static::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public static function bustCache(): void
    {
        Cache::forget('bet_time_settings.active');
    }

    /** All non-LO category names for this session (used for bet controls + result grid). */
    public function letters(): array
    {
        $all = array_merge($this->group1_types ?? [], $this->group2_types ?? []);
        $loNames = Cache::remember('bet_categories.lo_names', 300, fn (): array => BetCategory::where('type', BetCategory::TYPE_LO)->pluck('name')->all()
        );

        return array_values(array_filter($all, fn (string $t): bool => ! in_array($t, $loNames)));
    }

    /** Overall session status based on current time. */
    public function sessionStatus(): string
    {
        $now = now()->format('H:i:s');

        if ($now >= $this->result_time) {
            return 'done';
        }

        if ($now >= $this->group2_cutoff) {
            return 'closed';
        }

        if ($now >= $this->group1_cutoff) {
            return 'partial';
        }

        if ($now >= date('H:i:s', strtotime($this->group1_cutoff) - 600)) {
            return 'closing_soon';
        }

        return 'open';
    }

    /** Whether a specific letter/betType is still open for betting. */
    public function isBetAllowed(string $letter, string $betType): bool
    {
        $now = now()->format('H:i:s');
        $inGroup1 = in_array(strtoupper($letter), $this->group1_types)
                 || in_array(strtoupper($betType), $this->group1_types);
        $cutoff = $inGroup1 ? $this->group1_cutoff : $this->group2_cutoff;

        return $now < $cutoff;
    }
}
