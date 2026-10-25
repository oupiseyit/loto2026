<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BetTimeSetting extends Model
{
    protected $fillable = [
        'session_key',
        'lottery_type',
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

    /** Distinct lottery types (KH5D, VN5D, …) of the active draw times, in sort order. */
    public static function lotteryTypes(): array
    {
        return static::active()->pluck('lottery_type')->filter()->unique()->values()->all();
    }

    /** Display label for a ticket/result session key, e.g. "KH5D 10:35". */
    public static function label(string $sessionKey): string
    {
        // Includes inactive rows so old tickets (morning/noon/…) keep a readable label
        static $all = null;
        $all ??= static::all()->keyBy('session_key');
        $s = $all->get($sessionKey);

        return $s ? trim(($s->lottery_type ?: $s->session_name).' '.substr($s->result_time, 0, 5)) : $sessionKey;
    }

    /** All non-LO category names for this session (used for bet controls + result grid). */
    public function letters(): array
    {
        $all = array_merge($this->group1_types ?? [], $this->group2_types ?? []);
        $loNames = Cache::remember('bet_categories.lo_names', 300, fn (): array => BetCategory::where('type', BetCategory::TYPE_LO)->pluck('name')->all()
        );

        return array_values(array_unique(array_filter($all, fn (string $t): bool => ! in_array($t, $loNames))));
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
        $group1 = array_map('strtoupper', $this->group1_types);
        $inGroup1 = in_array(strtoupper($letter), $group1)
                 || in_array(strtoupper($betType), $group1);
        $cutoff = $inGroup1 ? $this->group1_cutoff : $this->group2_cutoff;

        return $now < $cutoff;
    }
}
