<?php

namespace App\Services;

use Carbon\Carbon;

class PublicHolidayService
{
    /** @var array<int, array<string, string>> */
    private static array $yearMaps = [];

    /**
     * @return array<string, string> Date (Y-m-d) => holiday name
     */
    public static function mapForYear(int $year): array
    {
        if (! isset(self::$yearMaps[$year])) {
            self::$yearMaps[$year] = self::buildYearMap($year);
        }

        return self::$yearMaps[$year];
    }

    /**
     * @return array<string, string> Date (Y-m-d) => holiday name
     */
    public static function mapForYearRange(int $startYear, int $endYear): array
    {
        $map = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $map += self::mapForYear($year);
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    public static function datesForYear(int $year): array
    {
        return array_keys(self::mapForYear($year));
    }

    /**
     * @return list<string>
     */
    public static function datesForYearRange(int $startYear, int $endYear): array
    {
        return array_keys(self::mapForYearRange($startYear, $endYear));
    }

    /**
     * @return array<int, list<string>>
     */
    public static function datesByYear(int $startYear, int $endYear): array
    {
        $byYear = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $byYear[$year] = self::datesForYear($year);
        }

        return $byYear;
    }

    public static function isHoliday(Carbon|string $date): bool
    {
        $day = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        return isset(self::mapForYear((int) $day->year)[$day->toDateString()]);
    }

    /**
     * South African public holidays (Public Holidays Act 36 of 1994).
     *
     * Computed locally so Yasumi does not scan hundreds of translation files
     * on every leave-balance / dashboard request.
     *
     * @return array<string, string>
     */
    private static function buildYearMap(int $year): array
    {
        $holidays = [];

        $add = static function (Carbon $date, string $name) use (&$holidays): void {
            $holidays[$date->toDateString()] = $name;
        };

        $fixed = [
            [1, 1, "New Year's Day"],
            [3, 21, 'Human Rights Day'],
            [4, 27, 'Freedom Day'],
            [5, 1, "Workers' Day"],
            [6, 16, 'Youth Day'],
            [8, 9, "National Women's Day"],
            [9, 24, 'Heritage Day'],
            [12, 16, 'Day of Reconciliation'],
            [12, 25, 'Christmas Day'],
            [12, 26, 'Day of Goodwill'],
        ];

        $sundayObserved = [];
        foreach ($fixed as [$month, $day, $name]) {
            $date = Carbon::create($year, $month, $day)->startOfDay();
            $add($date, $name);
            $sundayObserved[] = [$date, $name];
        }

        $easter = self::easterSunday($year);
        $add($easter->copy()->subDay(), 'Good Friday');
        $add($easter->copy()->addDay(), 'Family Day');

        foreach (self::specialHolidays($year) as $dateString => $name) {
            $add(Carbon::parse($dateString)->startOfDay(), $name);
        }

        // If a public holiday falls on a Sunday, the following Monday is also a public holiday.
        foreach ($sundayObserved as [$date, $name]) {
            if (! $date->isSunday()) {
                continue;
            }

            $observed = $date->copy()->addDay();
            if (! isset($holidays[$observed->toDateString()])) {
                $holidays[$observed->toDateString()] = $name.' (observed)';
                continue;
            }

            $next = $observed->copy()->addDay();
            if (! isset($holidays[$next->toDateString()])) {
                $holidays[$next->toDateString()] = $name.' (observed)';
            }
        }

        ksort($holidays);

        return $holidays;
    }

    /**
     * Once-off nationally proclaimed public holidays.
     *
     * @return array<string, string>
     */
    private static function specialHolidays(int $year): array
    {
        return match ($year) {
            2016 => ['2016-08-03' => 'Municipal Elections'],
            2019 => ['2019-05-08' => 'National and Provincial Government Elections'],
            2021 => ['2021-11-01' => 'Municipal Elections'],
            2022 => ['2022-12-27' => 'Public Holiday'],
            2023 => ['2023-12-15' => 'Public Holiday'],
            2024 => ['2024-05-29' => 'National and Provincial Government Elections'],
            default => [],
        };
    }

    /**
     * Anonymous Gregorian computus for Easter Sunday.
     */
    private static function easterSunday(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day)->startOfDay();
    }
}
