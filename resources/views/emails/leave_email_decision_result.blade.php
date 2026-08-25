@extends('layouts.letterhead-document')

@section('title', $title)

@section('content')
@php
    $submitted = !empty($submitted);
    $ok = !empty($ok);
@endphp

    <h1 class="lh-doc-title">{{ $title }}</h1>
    <div class="lh-center">
        @if (!empty($finalStatus))
            <p><span class="lh-pill {{ $ok || $submitted ? 'lh-pill--ok' : '' }}">{{ $finalStatus }}</span></p>
        @endif
        <p>{{ $message }}</p>
        @if ($submitted)
            <p><strong>Submitted</strong> — you can close this page.</p>
        @endif
        <p><a href="{{ url('/login') }}" class="lh-btn lh-btn--primary">Sign in to the Leave Management System</a></p>
    </div>
@endsection
