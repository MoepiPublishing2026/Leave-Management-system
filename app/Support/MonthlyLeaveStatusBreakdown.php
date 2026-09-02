<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class MonthlyLeaveStatusBreakdown
{
    /**
     * Build 12-month status series grouped by submission month ($monthColumn, usually created_at).
     * Pending rows with start_date before today count as overdue.
     *
     * @return array{
     *     monthlyApprovedData: array<int, int>,
     *     monthlyPendingData: array<int, int>,
     *     monthlyOverdueData: array<int, int>,
     *     monthlyRejectedData: array<int, int>,
     *     monthlyWithdrawnData: array<int, int>
     * }
     */
    public static function fromQuery(Builder $query, string $monthColumn = 'created_at', ?Carbon $asOfDate = null): array
    {
        $asOf = $asOfDate ?? Carbon::today();

        $records = (clone $query)->get([$monthColumn, 'status', 'start_date']);

        $series = [
            'approved' => array_fill(0, 12, 0),
            'pending' => array_fill(0, 12, 0),
            'overdue' => array_fill(0, 12, 0),
            'rejected' => array_fill(0, 12, 0),
            'withdrawn' => array_fill(0, 12, 0),
        ];

        foreach ($records as $record) {
            $dateValue = $record->{$monthColumn} ?? null;
            if ($dateValue === null || $dateValue === '') {
                continue;
            }

            $idx = (int) Carbon::parse($dateValue)->month - 1;
            if ($idx < 0 || $idx > 11) {
                continue;
            }

            $status = strtolower(trim((string) $record->status));
            if ($status === 'pending_senior') {
                $status = 'pending';
            }
            if (
                $status === 'pending'
                && $record->start_date
                && Carbon::parse($record->start_date)->lt($asOf)
            ) {
                $status = 'overdue';
            }

            if (array_key_exists($status, $series)) {
                $series[$status][$idx]++;
            }
        }

        return [
            'monthlyApprovedData' => $series['approved'],
            'monthlyPendingData' => $series['pending'],
            'monthlyOverdueData' => $series['overdue'],
            'monthlyRejectedData' => $series['rejected'],
            'monthlyWithdrawnData' => $series['withdrawn'],
        ];
    }
}
