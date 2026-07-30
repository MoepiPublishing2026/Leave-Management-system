<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leave status colors (matches dashboard status cards)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'all' => '#00C0A3',
        'pending' => '#C291A4',
        'pending_senior' => '#9B7EBD',
        'approved' => '#E27A53',
        'rejected' => '#87AFC7',
        'withdrawn' => '#D2B55B',
        'overdue' => '#C154C1',
        'on_leave' => '#D2B55B',
    ],

    'pie_labels' => ['Pending', 'Overdue', 'Rejected', 'Approved'],

    'monthly_status_labels' => ['Approved', 'Pending', 'Overdue', 'Rejected', 'Withdrawn'],

    'monthly_applications_line' => '#ec4899',
];
