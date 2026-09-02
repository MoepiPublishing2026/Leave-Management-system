@extends('emails.layouts.client-safe', [
    'title' => 'Leave request #'.($leaveRequest->id ?? ''),
    'preheader' => 'A leave request needs your decision.',
])

@section('content')
@php
    $fullName = $applicantName ?? (trim(($leaveRequest->user->first_name ?? '').' '.($leaveRequest->user->last_name ?? '')) ?: 'a staff member');
    $managerLabel = $managerName ?? '';
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    @if (!empty($isManagerApplicant))
        <p style="margin:0 0 16px;">A leave request from <strong>{{ $fullName }}</strong>, a manager assigned to you, needs your decision.</p>
    @else
        <p style="margin:0 0 16px;">A leave request from <strong>{{ $fullName }}</strong> needs your decision.</p>
        @if ($managerLabel !== '')
            <p style="margin:0 0 16px;">Their assigned manager is <strong>{{ $managerLabel }}</strong>.</p>
        @endif
    @endif
    <p style="margin:0 0 16px;">Open the full view to review the details and approve or reject, or open it in the app.</p>

    @include('emails.partials.document-links', [
        'documentLinks' => $documentLinks ?? [],
    ])

    @php
        $documentLinks = $documentLinks ?? [];
        $buttons = [];
        if (! empty($documentLinks[0]['viewUrl'] ?? null)) {
            $buttons[] = [
                'url' => $documentLinks[0]['viewUrl'],
                'label' => 'View document',
                'bg' => '#a2d109',
                'color' => '#0a1347',
            ];
        }
        $buttons[] = [
            'url' => $reviewUrl,
            'label' => 'Open full view',
            'bg' => '#a2d109',
            'color' => '#0a1347',
        ];
        $buttons[] = [
            'url' => $viewUrl,
            'label' => 'Open in app',
            'bg' => '#a2d109',
            'color' => '#0a1347',
        ];
        $links = [
            ['url' => $reviewUrl, 'label' => 'Open full view', 'color' => '#0a1347'],
            ['url' => $viewUrl, 'label' => 'Open in app', 'color' => '#0a1347'],
        ];
        foreach ($documentLinks as $file) {
            $links[] = ['url' => $file['viewUrl'], 'label' => 'View '.$file['label'], 'color' => '#0a1347'];
            $links[] = ['url' => $file['downloadUrl'], 'label' => 'Download '.$file['label'], 'color' => '#0a1347'];
        }
    @endphp

    @include('emails.partials.action-button-row', [
        'buttons' => $buttons,
    ])

    @include('emails.partials.action-fallback-links', [
        'links' => $links,
    ])
@endsection
