<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leave type colors
    |--------------------------------------------------------------------------
    |
    | Single source of truth for leave-type color mapping across:
    | - dashboards
    | - analytics/insights
    | - charts and legends
    |
    | Keys should be normalized (lowercase, no trailing " leave").
    */
    'colors' => [
        // Bright, distinct, non-repeating across leave types.
        'annual' => '#64748b',        // slate
        'sick' => '#a855f7',          // purple
        'maternity' => '#f59e0b',     // amber
        'paternity' => '#6366f1',     // indigo
        'bereavement' => '#ef4444',   // red
        'unpaid' => '#F08080',        // light coral
        'compassionate' => '#22c55e', // green
        'study' => '#14b8a6',         // teal
        'family responsibility' => '#0ea5e9', // sky
        'family' => '#0ea5e9',
        'urgent' => '#ec4899',        // pink
    ],

    // Fallback colors used when a leave type is not in the mapping.
    'fallback_palette' => [
        // Large bright palette for unknown/new leave types (minimize repetition).
        '#2563eb', // blue
        '#06b6d4', // cyan
        '#14b8a6', // teal
        '#22c55e', // green
        '#84cc16', // lime
        '#f59e0b', // amber
        '#f97316', // orange
        '#ef4444', // red
        '#ec4899', // pink
        '#a855f7', // purple
        '#8b5cf6', // violet
        '#6366f1', // indigo
        '#0ea5e9', // sky
        '#d946ef', // fuchsia
        '#64748b', // slate (last resort neutral)
    ],
];

