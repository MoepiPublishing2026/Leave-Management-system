@extends('emails.layouts.client-safe', [
    'title' => 'Performance review',
    'preheader' => 'A monthly performance review is ready for you.',
])

@section('content')
    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">A monthly performance review for <strong>{{ $review->employee_name }}</strong> is ready for your scores.</p>
    @if (!empty($managerName))
        <p style="margin:0 0 16px;">Their assigned manager is <strong>{{ $managerName }}</strong>.</p>
    @endif
    <p style="margin:0 0 16px;">Open the full view to complete it, or open it in the app.</p>

    @include('emails.partials.action-button-row', [
        'buttons' => [
            [
                'url' => $reviewUrl,
                'label' => 'Open full view',
                'bg' => '#a2d109',
                'color' => '#0a1347',
            ],
            [
                'url' => $appUrl,
                'label' => 'Open in app',
                'bg' => '#a2d109',
                'color' => '#0a1347',
            ],
        ],
    ])

    @include('emails.partials.action-fallback-links', [
        'links' => [
            ['url' => $reviewUrl, 'label' => 'Open full view', 'color' => '#0a1347'],
            ['url' => $appUrl, 'label' => 'Open in app', 'color' => '#0a1347'],
        ],
    ])
@endsection
