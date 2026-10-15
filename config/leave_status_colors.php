<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leave status colors (matches dashboard status cards)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'all'      => '#4de8c8',
        'pending'  => '#f4b86a',
        'overdue'  => '#b08cf5',
        'approved' => '#a23d85',
        'rejected' => '#8fc4c7',
        'on_leave' => '#bad84e',
    ],

    'pie_labels' => ['Pending', 'Overdue', 'Rejected', 'Approved'],

    'monthly_status_labels' => ['Approved', 'Pending', 'Overdue', 'Rejected', 'Withdrawn'],

    'monthly_applications_line' => '#ec4899',
];
