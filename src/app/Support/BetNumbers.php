<?php

namespace App\Support;

/**
 * Expands one bet-form entry into the list of numbers it covers.
 * Mirrored in JS by betBreakdown() in resources/views/livewire/bet-form.blade.php — keep both in sync.
 */
class BetNumbers
{
    public const NORMAL = 1;  // Normal: the number itself

    public const MULTIPLY = 2;  // Xគុណ: every unique digit permutation

    public const RANGE = 3;  // <=>: លេខ → ដល់លេខ

    public const PAIR = 4;  // Kគូ: every repdigit (00, 11 … 99 or 000 … 999)

    public const ROLL = 5;  // Wអូស: every 2-digit number containing the digit

    /** @return list<string> empty when the input is invalid for the mode */
    public static function expand(int $mode, string $number, string $end = ''): array
    {
        if (! ctype_digit($number.$end) && $mode !== self::PAIR) {
            return [];
        }
        $len = strlen($number);

        return match ($mode) {
            self::NORMAL => $len >= 1 && $len <= 3 ? [$number] : [],
            self::MULTIPLY => $len >= 2 && $len <= 3 ? self::permutations($number) : [],
            self::RANGE => self::range($number, $end),
            self::PAIR => self::pairs($number === '' ? 2 : $len),
            self::ROLL => $len === 1 ? self::roll($number) : [],
            default => [],
        };
    }

    private static function permutations(string $digits): array
    {
        if (strlen($digits) <= 1) {
            return [$digits];
        }
        $out = [];
        foreach (str_split($digits) as $i => $d) {
            foreach (self::permutations(substr_replace($digits, '', $i, 1)) as $rest) {
                $out[$d.$rest] = true;
            }
        }

        return array_map('strval', array_keys($out));
    }

    /** One differing digit → sweep that digit; otherwise every number from start to end. */
    private static function range(string $start, string $end): array
    {
        $len = strlen($start);
        if ($len < 2 || $len > 3 || strlen($end) !== $len || (int) $end <= (int) $start) {
            return [];
        }
        $diff = array_keys(array_diff_assoc(str_split($start), str_split($end)));
        $step = count($diff) === 1 ? 10 ** ($len - 1 - $diff[0]) : 1;

        $out = [];
        for ($n = (int) $start; $n <= (int) $end; $n += $step) {
            $out[] = str_pad((string) $n, $len, '0', STR_PAD_LEFT);
        }

        return $out;
    }

    private static function pairs(int $len): array
    {
        return $len === 2 || $len === 3 ? array_map(fn (int $d): string => str_repeat((string) $d, $len), range(0, 9)) : [];
    }

    private static function roll(string $d): array
    {
        $out = [];
        foreach (range(0, 9) as $x) {
            $out[$d.$x] = true;
            $out[$x.$d] = true;
        }

        return array_map('strval', array_keys($out));
    }
}
