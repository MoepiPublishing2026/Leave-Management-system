@extends('emails.layouts.client-safe', [
    'title' => 'Welcome to Leave Management',
    'preheader' => 'Your account has been created.',
])

@section('content')
    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::helloUser($user) }}</p>
    <p style="margin:0 0 16px;">Your account has been created. Your login details are:</p>

    @include('emails.partials.details-box', [
        'rows' => [
            ['label' => 'Email', 'value' => $user->email],
            ['label' => 'Password', 'value' => $password],
        ],
    ])

    @include('emails.partials.primary-button', ['url' => url('/login')])
@endsection
