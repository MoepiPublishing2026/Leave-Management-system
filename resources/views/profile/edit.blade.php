@php
    $profileLayout = match (strtolower((string) (auth()->user()->role ?? ''))) {
        'admin' => 'layouts.admin',
        'manager' => 'layouts.manager',
        default => 'layouts.app',
    };
@endphp
@extends($profileLayout)

@section('title', 'Profile')

@section('content')
@include('partials.content-page-styles')
<style>
    .profile-picture-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
    }
    .profile-picture-preview,
    .profile-picture-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 3px solid #86efac;
    }
    .profile-picture-preview {
        object-fit: cover;
        display: block;
    }
    .profile-picture-placeholder {
        background: #ecfdf5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #138056;
        font-size: 2rem;
    }
    .profile-picture-actions {
        flex: 1;
        min-width: 220px;
    }
    .profile-picture-input {
        display: none;
    }
    .profile-picture-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 8px;
    }
    .app-page-btn--danger {
        background: #dc2626;
        border: 1px solid #b91c1c;
        color: #fff;
    }
    .app-page-btn--danger:hover {
        background: #b91c1c;
        color: #fff;
    }
    .profile-flash {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        background: #dcfce7;
        color: #166534;
        font-weight: 600;
        font-size: 0.92rem;
    }
</style>

<div class="app-page">
    <h1>Profile</h1>
    <p class="app-page-lead">Update your password and profile picture. Name and email are managed by your administrator.</p>

    @if(session('success'))
        <div class="profile-flash">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="app-page-panel">
            <h2 class="app-page-section-title">Account</h2>
            <div class="app-form-grid">
                <div class="app-form-group">
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" readonly />
                </div>
                <div class="app-form-group">
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" readonly />
                </div>
                <div class="app-form-group" style="grid-column: 1 / -1;">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" readonly />
                </div>
            </div>
        </div>

        <div class="app-page-panel">
            <h2 class="app-page-section-title">Security</h2>
            <div class="app-form-grid">
                <div class="app-form-group" style="grid-column: 1 / -1;">
                    <label for="current_password">Current password</label>
                    <div class="app-password-wrap">
                        <input id="current_password" name="current_password" type="password" placeholder="Required to change password" autocomplete="current-password" />
                        <button type="button" class="app-password-toggle" id="toggleCurrentPassword" aria-label="Show or hide current password">
                            <svg id="eyeOpenCurrent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg id="eyeClosedCurrent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" /></svg>
                        </button>
                    </div>
                    @error('current_password')<div class="app-form-error">{{ $message }}</div>@enderror
                </div>
                <div class="app-form-group">
                    <label for="password">New password</label>
                    <div class="app-password-wrap">
                        <input id="password" name="password" type="password" placeholder="Leave blank to keep current" autocomplete="new-password" />
                        <button type="button" class="app-password-toggle" id="togglePassword" aria-label="Show or hide new password">
                            <svg id="eyeOpenPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg id="eyeClosedPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" /></svg>
                        </button>
                    </div>
                    @error('password')<div class="app-form-error">{{ $message }}</div>@enderror
                </div>
                <div class="app-form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <div class="app-password-wrap">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                        <button type="button" class="app-password-toggle" id="togglePasswordConfirmation" aria-label="Show or hide confirm password">
                            <svg id="eyeOpenPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg id="eyeClosedPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-page-panel">
            <h2 class="app-page-section-title">Profile picture</h2>
            <div class="profile-picture-row">
                <div class="profile-picture-preview-wrap">
                    @if ($user->profile_picture)
                        <img id="profilePreview" src="{{ route('users.profile_picture', ['user' => $user->id]) }}?v={{ optional($user->updated_at)->timestamp }}" alt="Profile picture" class="profile-picture-preview" />
                    @else
                        <div id="profilePreviewPlaceholder" class="profile-picture-placeholder">
                            {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                        </div>
                        <img id="profilePreview" alt="Profile picture preview" class="profile-picture-preview" style="display:none;" />
                    @endif
                </div>
                <div class="profile-picture-actions">
                    <input id="profile_picture" name="profile_picture" type="file" accept="image/*" class="profile-picture-input" />
                    <div class="profile-picture-buttons">
                        <button type="button" class="app-page-btn app-page-btn--secondary" id="profilePictureChooseBtn">
                            {{ $user->profile_picture ? 'Replace photo' : 'Upload photo' }}
                        </button>
                        @if ($user->profile_picture)
                            <button type="button" class="app-page-btn app-page-btn--danger" id="profilePictureRemoveBtn">Remove photo</button>
                        @endif
                    </div>
                    <span class="app-form-hint">JPG, PNG, or WebP up to 10 MB. Large photos are resized automatically.</span>
                </div>
            </div>
        </div>

        <button type="submit" class="app-page-btn app-page-btn--primary">Save changes</button>
    </form>

    @if ($user->profile_picture)
        <form id="profilePictureRemoveForm" method="POST" action="{{ route('profile.picture.destroy') }}" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>

<script>
    function setupToggle(inputId, toggleId, openId, closedId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);
        const eyeOpen = document.getElementById(openId);
        const eyeClosed = document.getElementById(closedId);
        if (!toggle || !input || !eyeOpen || !eyeClosed) return;
        function flip() {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeOpen.style.display = isPassword ? 'none' : 'block';
            eyeClosed.style.display = isPassword ? 'block' : 'none';
        }
        toggle.addEventListener('click', (e) => { e.preventDefault(); flip(); });
    }
    setupToggle('current_password', 'toggleCurrentPassword', 'eyeOpenCurrent', 'eyeClosedCurrent');
    setupToggle('password', 'togglePassword', 'eyeOpenPassword', 'eyeClosedPassword');
    setupToggle('password_confirmation', 'togglePasswordConfirmation', 'eyeOpenPasswordConfirmation', 'eyeClosedPasswordConfirmation');

    document.getElementById('profile_picture')?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const preview = document.getElementById('profilePreview');
        const placeholder = document.getElementById('profilePreviewPlaceholder');
        if (!preview) return;
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
        const chooseBtn = document.getElementById('profilePictureChooseBtn');
        if (chooseBtn) chooseBtn.textContent = 'Replace photo';
    });

    document.getElementById('profilePictureChooseBtn')?.addEventListener('click', function () {
        document.getElementById('profile_picture')?.click();
    });

    document.getElementById('profilePictureRemoveBtn')?.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Remove profile picture?',
            text: 'Your photo will be removed from your account.',
            showCancelButton: true,
            confirmButtonText: 'Remove',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('profilePictureRemoveForm')?.submit();
            }
        });
    });
</script>
@endsection
