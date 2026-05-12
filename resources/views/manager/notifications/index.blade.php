@extends('layouts.manager')

@section('title', 'Notifications')

@section('content')
<div class="container my-4">
    <h1>All Notifications</h1>

    @if ($notifications->isEmpty())
        <p>You have no notifications.</p>
    @else
        <ul class="list-group">
            @foreach ($notifications as $notification)
                <li class="list-group-item @if(is_null($notification->read_at)) list-group-item-warning @endif">
                    <a href="{{ route('notifications.open', ['notification' => $notification->id, 'redirect' => route('notifications.index')]) }}" style="text-decoration:none; color:inherit;">
                        {{ $notification->data['message'] ?? 'No message' }} - <small>{{ $notification->created_at->diffForHumans() }}</small>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

