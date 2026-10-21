<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeaveTypeChartCounts
{
    /**
     * Count requests by current leave type, including Unpaid.
     */
    public static function fromQuery(Builder $query): array
    {
        $rows = (clone $query)
            ->toBase()
            ->selectRaw('leave_requests.leave_type as leave_type, COUNT(*) as total')
            ->groupBy('leave_requests.leave_type')
            ->get();

        $filtered = [];
        $canonical = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row->leave_type ?? ''));
            if ($label === '') {
                continue;
            }
            $fold = strtolower($label);
            if (! isset($canonical[$fold])) {
                $canonical[$fold] = $label;
            }
            $key = $canonical[$fold];
            $filtered[$key] = ($filtered[$key] ?? 0) + (int) $row->total;
        }
        arsort($filtered, SORT_NUMERIC);

        return $filtered;
    }

    public static function payStatusFromQuery(Builder $query): array
    {
        $unpaid = (clone $query)
            ->whereRaw("LOWER(TRIM(COALESCE(leave_type, ''))) = ?", ['unpaid'])
            ->count();
        $total = (clone $query)->count();

        return [
            'Paid' => max(0, $total - $unpaid),
            'Unpaid' => (int) $unpaid,
        ];
    }

    public static function toRows(array $counts): Collection
    {
        return collect($counts)
            ->map(fn (int $total, string $type) => (object) [
                'leave_type' => $type,
                'total' => $total,
            ])
            ->values();
    }
}
