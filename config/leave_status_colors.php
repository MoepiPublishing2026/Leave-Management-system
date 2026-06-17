<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leave status colors (single source of truth)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'pending' => '#facc15',
        'approved' => '#10b981',
        'rejected' => '#64748b',   // muted slate (not bright cyan/red)
        'withdrawn' => '#2dd4bf',  // soft teal (lighter than before)
        'overdue' => '#C154C1',
    ],

    'pie_labels' => ['Pending', 'Overdue', 'Rejected', 'Approved'],

    'monthly_status_labels' => ['Approved', 'Pending', 'Overdue', 'Rejected', 'Withdrawn'],

    'monthly_applications_line' => '#ec4899',
];
