<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    public static function resolveMonthYear(int $month, int $year): array
    {
        return [
            max(1, min(12, $month)),
            max(1970, min(2100, $year)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function monthContext(int $month, int $year): array
    {
        [$month, $year] = self::resolveMonthYear($month, $year);

        $firstDay = Carbon::create($year, $month, 1);
        $startOfMonth = $firstDay->copy()->startOfMonth();
        $endOfMonth = $firstDay->copy()->endOfMonth();

        return [
            'month' => $month,
            'year' => $year,
            'startDay' => $firstDay->dayOfWeek,
            'daysInMonth' => $firstDay->daysInMonth,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'publicHolidays' => PublicHolidayService::mapForYearRange(
                $startOfMonth->year,
                $endOfMonth->year
            ),
            'today' => Carbon::today()->toDateString(),
        ];
    }

    /**
     * @return array<string, list<array<string, string>>>
     */
    public static function buildEvents(
        Collection $leaves,
        Carbon $startOfMonth,
        Carbon $endOfMonth,
        array $publicHolidays,
        bool $includeUser = true
    ): array {
        $events = [];

        foreach ($leaves as $leave) {
            $period = Carbon::parse($leave->start_date)->daysUntil(Carbon::parse($leave->end_date));

            foreach ($period as $day) {
                if (
                    !$day->between($startOfMonth, $endOfMonth)
                    || $day->isWeekend()
                    || isset($publicHolidays[$day->toDateString()])
                ) {
                    continue;
                }

                $dateKey = $day->format('Y-m-d');
                $typeLabel = ucfirst((string) $leave->leave_type);
                $entry = [
                    'type' => $typeLabel,
                    'type_key' => LeaveTypeColorService::normalizeKey($leave->leave_type),
                    'color' => LeaveTypeColorService::colorForType($leave->leave_type),
                ];

                if ($includeUser) {
                    $entry['user'] = $leave->user
                        ? trim($leave->user->first_name . ' ' . $leave->user->last_name)
                        : 'Unknown';
                    $dedupeKey = ($leave->user_id ?? 'unknown') . '|' . $entry['type_key'];

                    $existingKeys = array_column($events[$dateKey] ?? [], 'dedupe_key');
                    if (in_array($dedupeKey, $existingKeys, true)) {
                        continue;
                    }

                    $entry['dedupe_key'] = $dedupeKey;
                }

                $events[$dateKey][] = $entry;
            }
        }

        return $events;
    }

    /**
     * @param array<string, list<array<string, string>>> $events
     * @return array{days_with_leave: int, total_entries: int}
     */
    public static function monthSummary(array $events): array
    {
        $totalEntries = 0;
        foreach ($events as $dayEvents) {
            $totalEntries += count($dayEvents);
        }

        return [
            'days_with_leave' => count($events),
            'total_entries' => $totalEntries,
        ];
    }
}
