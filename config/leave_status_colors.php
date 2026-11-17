<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leave status colors (Manager Data Dictionary)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'all'      => '#4ee5c5',
        'pending'  => '#f3b86b',
        'overdue'  => '#ae8df5',
        'approved' => '#a33d86',
        'rejected' => '#8fc4c7',
        'on_leave' => '#bad550',
        'withdrawn' => '#fbe8b0',
    ],

    'pie_labels' => ['Pending', 'Overdue', 'Rejected', 'Approved'],

    'monthly_status_labels' => ['Approved', 'Pending', 'Overdue', 'Rejected', 'Withdrawn'],

    /* Manager team dashboard charts */
    'leave_type' => [
        'Sick' => '#4a7ff7',
        'Maternity' => '#ae8df5',
        'Paternity' => '#ae8df5',
        'Paid' => '#f3b86b',
        'Annual' => '#f3b86b',
        'Study' => '#ffde59',
    ],

    'status_pie' => [
        'Pending' => '#4a7ff7',
        'Approved' => '#ae8df5',
        'Overdue' => '#ffde59',
        'Rejected' => '#8fc4c7',
    ],

    /* My Leave Dashboard pie (personal) */
    'status_pie_personal' => [
        'Pending' => '#ae8df5',
        'Approved' => '#ffde59',
        'Rejected' => '#4a7ff7',
        'Overdue' => '#f3b86b',
    ],

    'paid_unpaid' => [
        'Paid' => '#4a7ff7',
        'Unpaid' => '#ae8df5',
    ],

    'monthly_applications_line' => '#4a7ff7',
    'department_bar' => '#4a7ff7',
];
