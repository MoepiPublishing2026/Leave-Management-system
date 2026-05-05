<?php

namespace App\Support;

/**
 * Stable percentage breakdowns that sum to exactly 100.0 (one decimal).
 * Uses the largest remainder method at 0.1% precision so displayed values
 * match across pages and do not depend on slice ordering.
 */
final class PercentageDistribution
{
    /**
     * @param  array<string|int, float|int>  $counts  Non-negative counts keyed by label/id
     * @return array<string|int, float>  Percentages with one decimal place, summing to 100.0
     */
    public static function oneDecimalPercents(array $counts): array
    {
        $counts = array_map(static fn ($v) => max(0, (float) $v), $counts);
        $total = array_sum($counts);

        if ($total <= 0) {
            return array_map(static fn () => 0.0, $counts);
        }

        // 1000 units == 100.0%
        $floors = [];
        $remainders = [];
        foreach ($counts as $key => $c) {
            $exact = ($c / $total) * 1000;
            $floor = (int) floor($exact + 1e-9);
            $floors[$key] = $floor;
            $remainders[$key] = $exact - $floor;
        }

        $need = 1000 - array_sum($floors);
        $keys = array_keys($counts);
        usort($keys, function ($a, $b) use ($remainders) {
            $cmp = $remainders[$b] <=> $remainders[$a];
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp((string) $a, (string) $b);
        });

        $extras = array_fill_keys(array_keys($counts), 0);
        for ($i = 0; $i < $need; $i++) {
            $extras[$keys[$i]]++;
        }

        $out = [];
        foreach (array_keys($counts) as $key) {
            $out[$key] = round(($floors[$key] + $extras[$key]) / 10, 1);
        }

        return $out;
    }
}
