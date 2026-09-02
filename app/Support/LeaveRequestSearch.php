<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestSearch
{
    /**
     * Match any column, then sort by left-to-right column priority.
     */
    public static function apply(Builder $query, string $search, bool $includeUserFields = true): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $table = $query->getModel()->getTable();

        $query->where(function (Builder $q) use ($search, $includeUserFields, $table) {
            self::applyAnyColumnMatch($q, $search, $includeUserFields, $table);
        });

        [$prioritySql, $priorityBindings] = self::priorityOrderSql($search, $includeUserFields, $table);
        $query->reorder()->orderByRaw($prioritySql, $priorityBindings)->orderByDesc("{$table}.created_at");
    }

    private static function applyAnyColumnMatch(
        Builder $query,
        string $search,
        bool $includeUserFields,
        string $table
    ): void {
        $query->where(function (Builder $q) use ($search, $includeUserFields, $table) {
            $q->where("{$table}.leave_type", 'like', '%' . $search . '%')
                ->orWhereRaw('LOWER(' . $table . '.status) like ?', ['%' . strtolower($search) . '%'])
                ->orWhere("{$table}.important_comment", 'like', '%' . $search . '%');

            self::applyDateColumnSearch($q, 'start_date', $search, $table);
            self::applyDateColumnSearch($q, 'end_date', $search, $table);
            self::applyDateColumnSearch($q, 'created_at', $search, $table);

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
                $q->orWhere("{$table}.id", (int) $search);
            }
        });
    }

    /**
     * @return array{0: string, 1: list<mixed>}
     */
    private static function priorityOrderSql(string $search, bool $includeUserFields, string $table): array
    {
        $like = '%' . $search . '%';
        $likeLower = '%' . strtolower($search) . '%';
        $bindings = [];
        $parts = [];
        $priority = 1;

        if (ctype_digit($search)) {
            $parts[] = "CASE WHEN {$table}.id = ? THEN {$priority} ELSE 99 END";
            $bindings[] = (int) $search;
            $priority++;
        }

        if ($includeUserFields) {
            $parts[] = "CASE WHEN EXISTS (
                SELECT 1 FROM users
                WHERE users.id = {$table}.user_id
                AND (
                    users.first_name LIKE ?
                    OR users.last_name LIKE ?
                    OR users.email LIKE ?
                    OR users.department LIKE ?
                    OR users.designation LIKE ?
                )
            ) THEN {$priority} ELSE 99 END";
            array_push($bindings, $like, $like, $like, $like, $like);
            $priority++;
        }

        $parts[] = "CASE WHEN {$table}.leave_type LIKE ? THEN {$priority} ELSE 99 END";
        $bindings[] = $like;
        $priority++;

        foreach (['start_date', 'end_date', 'created_at'] as $column) {
            [$dateCond, $dateBindings] = self::dateColumnMatchCondition($table, $column, $search);
            $parts[] = "CASE WHEN ({$dateCond}) THEN {$priority} ELSE 99 END";
            array_push($bindings, ...$dateBindings);
            $priority++;
        }

        $parts[] = "CASE WHEN LOWER({$table}.status) LIKE ? THEN {$priority} ELSE 99 END";
        $bindings[] = $likeLower;
        $priority++;

        $parts[] = "CASE WHEN {$table}.important_comment LIKE ? THEN {$priority} ELSE 99 END";
        $bindings[] = $like;

        return ['LEAST(' . implode(', ', $parts) . ')', $bindings];
    }

    /**
     * @return array{0: string, 1: list<mixed>}
     */
    private static function dateColumnMatchCondition(string $table, string $column, string $search): array
    {
        $like = '%' . $search . '%';
        $bindings = [$like];
        // Compare the date only so a search like "27" matches 27 Aug, not 11:27.
        $conditions = ["DATE({$table}.{$column}) LIKE ?"];

        $formats = [
            '%e %M %Y',
            '%d %M %Y',
            '%e %b %Y',
            '%d %b %Y',
            '%M %Y',
            '%b %Y',
            '%d/%m/%Y',
            '%m/%d/%Y',
        ];

        foreach ($formats as $format) {
            $conditions[] = "DATE_FORMAT({$table}.{$column}, '{$format}') LIKE ?";
            $bindings[] = $like;
        }

        foreach (self::parseDates($search) as $ymd) {
            $conditions[] = "DATE({$table}.{$column}) = ?";
            $bindings[] = $ymd;
        }

        return [implode(' OR ', $conditions), $bindings];
    }

    private static function applyDateColumnSearch(Builder $query, string $column, string $search, string $table): void
    {
        [$condition, $bindings] = self::dateColumnMatchCondition($table, $column, $search);

        $query->orWhereRaw('(' . $condition . ')', $bindings);
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
