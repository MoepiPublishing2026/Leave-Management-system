<?php

namespace App\Services;

class LeaveTypeColorService
{
    public static function normalizeKey(?string $value): string
    {
        $v = strtolower(trim((string) $value));
        if ($v === '') {
            return '';
        }
        if (str_ends_with($v, ' leave')) {
            $v = trim(substr($v, 0, -5));
        }

        return $v;
    }

    public static function colorForType(?string $type): string
    {
        $key = self::normalizeKey($type);
        $fixed = config('leave_type_colors.colors', []);

        if ($key !== '' && array_key_exists($key, $fixed)) {
            return $fixed[$key];
        }

        $palette = config('leave_type_colors.fallback_palette', ['#64748b']);
        $index = $key === '' ? 0 : (self::hashString($key) % max(count($palette), 1));

        return $palette[$index] ?? '#64748b';
    }

    /**
     * @return list<array{label: string, color: string, key: string}>
     */
    public static function legendItems(?array $extraTypes = null): array
    {
        $types = collect();

        if (class_exists(\App\Models\LeavePolicy::class)) {
            $types = $types->merge(
                \App\Models\LeavePolicy::query()->orderBy('leave_type')->pluck('leave_type')
            );
        }

        $types = $types->merge(array_keys(config('leave_type_colors.colors', [])));
        if ($extraTypes) {
            $types = $types->merge($extraTypes);
        }

        return $types
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->unique(fn ($t) => self::normalizeKey($t))
            ->sort()
            ->values()
            ->map(function ($type) {
                $key = self::normalizeKey($type);

                return [
                    'key' => $key,
                    'label' => $type,
                    'color' => self::colorForType($type),
                ];
            })
            ->all();
    }

    public static function tintBackground(string $hex, int $mixPercent = 38): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return '#e8f4ec';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $ratio = max(0, min(100, $mixPercent)) / 100;

        $r = (int) round($r * $ratio + 255 * (1 - $ratio));
        $g = (int) round($g * $ratio + 255 * (1 - $ratio));
        $b = (int) round($b * $ratio + 255 * (1 - $ratio));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private static function hashString(string $str): int
    {
        $h = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $h = (($h << 5) - $h) + ord($str[$i]);
            $h &= 0xFFFFFFFF;
        }

        return abs((int) $h);
    }
}
