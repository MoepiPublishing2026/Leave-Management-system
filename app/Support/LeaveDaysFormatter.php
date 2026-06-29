<?php

namespace App\Support;

class LeaveDaysFormatter
{
    public static function format(float|int|string|null $days): string
    {
        if ($days === '' || $days === null) {
            return '';
        }

        $n = (float) $days;

        return rtrim(rtrim(number_format($n, 1, '.', ''), '0'), '.');
    }
}
