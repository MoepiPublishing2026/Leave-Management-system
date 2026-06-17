<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestSearch
{
    /**
     * Apply text search including human-readable leave dates (e.g. "27 May 2025").
     */
    public static function apply(Builder $query, string $search, bool $includeUserFields = true): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search, $includeUserFields) {
            $q->where('leave_type', 'like', '%' . $search . '%')
                ->orWhereRaw('LOWER(status) like ?', ['%' . strtolower($search) . '%'])
                ->orWhere('important_comment', 'like', '%' . $search . '%');

            self::applyDateColumnSearch($q, 'start_date', $search);
            self::applyDateColumnSearch($q, 'end_date', $search);

            if ($includeUserFields) {
                $q->orWhereHas('user', function (Builder $q2) use ($search) {
                    $q2->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('department', 'like', '%' . $search . '%')
                        ->orWhere('designation', 'like', '%' . $search . '%');
                });
            }

            if (ctype_digit($search)) {
                $q->orWhere('id', (int) $search);
            }
        });
    }

    private static function applyDateColumnSearch(Builder $query, string $column, string $search): void
    {
        $query->orWhere($column, 'like', '%' . $search . '%');

        $like = '%' . $search . '%';
        $query->orWhereRaw("DATE_FORMAT({$column}, '%e %M %Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%d %M %Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%e %b %Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%d %b %Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%M %Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%b %Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%d/%m/%Y') LIKE ?", [$like])
            ->orWhereRaw("DATE_FORMAT({$column}, '%m/%d/%Y') LIKE ?", [$like]);

        foreach (self::parseDates($search) as $ymd) {
            $query->orWhereDate($column, $ymd);
        }
    }

    /**
     * @return list<string> Dates as Y-m-d
     */
    private static function parseDates(string $search): array
    {
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'd-m-Y',
            'm-d-Y',
            'd M Y',
            'j M Y',
            'd F Y',
            'j F Y',
            'M d Y',
            'F d Y',
            'M Y',
            'F Y',
        ];

        $found = [];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $search);
                if ($parsed !== false) {
                    $found[] = $parsed->toDateString();
                }
            } catch (\Throwable) {
                // try next format
            }
        }

        try {
            $parsed = Carbon::parse($search);
            if ($parsed->year >= 1970) {
                $found[] = $parsed->toDateString();
            }
        } catch (\Throwable) {
            // not a parseable date
        }

        return array_values(array_unique($found));
    }
}
