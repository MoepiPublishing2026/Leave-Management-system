<?php

namespace App\Services;

use Yasumi\Yasumi;

class PublicHolidayService
{
    /**
     * @return array<string, string> Date (Y-m-d) => holiday name
     */
    public static function mapForYearRange(int $startYear, int $endYear): array
    {
        $map = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            foreach (Yasumi::create('SouthAfrica', $year) as $holiday) {
                $map[$holiday->format('Y-m-d')] = $holiday->getName();
            }
        }

        return $map;
    }
}
