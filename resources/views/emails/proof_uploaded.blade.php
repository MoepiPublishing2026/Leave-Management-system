@extends('emails.layouts.client-safe', [
    'title' => ($isDoctorsLetter ?? false) ? 'Doctor\'s letter uploaded' : 'Documents updated',
    'preheader' => $messageBody ?? 'A supporting document was uploaded.',
])

@section('content')
@php
    $documentLinks = $documentLinks ?? [];
    $isDoctorsLetter = (bool) ($isDoctorsLetter ?? false);
    $firstViewUrl = $documentLinks[0]['viewUrl'] ?? null;
    $reviewUrl = $reviewUrl ?? null;
    $buttons = [];
    if (! empty($firstViewUrl)) {
        $buttons[] = [
            'url' => $firstViewUrl,
            'label' => $isDoctorsLetter ? "View doctor's letter" : 'View document',
            'bg' => '#a2d109',
            'color' => '#0a1347',
        ];
    }
    if (! empty($reviewUrl)) {
        $buttons[] = [
            'url' => $reviewUrl,
            'label' => 'Open full view',
            'bg' => '#a2d109',
            'color' => '#0a1347',
        ];
    }
    $buttons[] = [
        'url' => $openInAppUrl,
        'label' => 'Open in app',
        'bg' => '#a2d109',
        'color' => '#0a1347',
    ];
    $links = [];
    foreach ($documentLinks as $file) {
        $links[] = ['url' => $file['viewUrl'], 'label' => 'View '.$file['label'], 'color' => '#0a1347'];
        $links[] = ['url' => $file['downloadUrl'], 'label' => 'Download '.$file['label'], 'color' => '#0a1347'];
    }
    if (! empty($reviewUrl)) {
        $links[] = ['url' => $reviewUrl, 'label' => 'Open full view', 'color' => '#0a1347'];
    }
    $links[] = ['url' => $openInAppUrl, 'label' => 'Open in app', 'color' => '#0a1347'];
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">{{ $messageBody }}</p>
    <p style="margin:0 0 16px;">
        Request <strong>#{{ $leaveRequest->id }}</strong> —
        {{ $leaveRequest->leave_type }} leave from {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}.
    </p>

    @include('emails.partials.document-links', [
        'documentLinks' => $documentLinks,
        'documentNote' => 'The file is also attached to this email when it is small enough to send.',
    ])

    @include('emails.partials.action-button-row', ['buttons' => $buttons])
    @include('emails.partials.action-fallback-links', ['links' => $links])
@endsection
