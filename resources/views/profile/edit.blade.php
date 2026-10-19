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
    .app-page.profile-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 4px 32px;
        color: #243044;
        font-family: "Poppins", "Segoe UI", sans-serif;
        box-sizing: border-box;
    }
    html body main .profile-page h1 {
        margin: 0 0 6px;
        color: #243044;
        font-size: 36px;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.15;
        font-family: "Poppins", "Segoe UI", sans-serif;
    }
    .profile-page .app-page-lead {
        margin: 0 0 22px;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
        font-weight: 400;
    }
    .profile-page > form {
        display: grid;
        gap: 18px;
    }
    .profile-page .app-page-panel {
        padding: 22px 24px;
        border: 1px solid #d5dbe3;
        border-radius: 16px;
        background: #fff;
        box-shadow: none;
        margin-bottom: 0;
    }
    .profile-page .app-page-section-title {
        margin: 0 0 14px;
        color: #243044;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .profile-page .app-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px 20px;
    }
    html body main .profile-page .app-form-group label {
        display: block;
        margin: 0 0 8px;
        color: #243044;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    html body main .profile-page .app-form-group input,
    html body main .profile-page .app-password-wrap,
    html body main .profile-page .app-password-wrap input,
    html body main .profile-page .app-form-group input:focus,
    html body main .profile-page .app-password-wrap input:focus {
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
    }
    html body main .profile-page .app-form-group input,
    html body main .profile-page .app-password-wrap input {
        width: 100%;
        height: 46px !important;
        min-height: 46px !important;
        padding: 0 16px !important;
        border-radius: 10px;
        background: #e6f2f2 !important;
        color: #6b7280 !important;
        font-family: inherit;
        font-size: 15px !important;
        font-weight: 500;
        box-sizing: border-box;
    }
    html body main .profile-page .app-password-wrap {
        background: #e6f2f2;
        border-radius: 10px;
        position: relative;
    }
    html body main .profile-page .app-password-wrap input {
        padding-right: 42px !important;
        background: transparent !important;
    }
    .profile-page .app-password-toggle {
        right: 10px;
        background: transparent !important;
        border: 0 !important;
        min-width: 0 !important;
        width: auto !important;
        height: auto !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
    .profile-page .profile-picture-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 20px 32px;
    }
    .profile-page .profile-picture-preview-wrap {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    .profile-page .profile-picture-preview,
    .profile-page .profile-picture-placeholder {
        width: 104px;
        height: 104px;
        border-radius: 50%;
        border: 3px solid #287fb1;
        box-sizing: border-box;
    }
    .profile-page .profile-picture-preview {
        object-fit: cover;
        display: block;
    }
    .profile-page .profile-picture-placeholder {
        background: #c8d3dc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #287fb1;
        font-size: 32px;
    }
    .profile-page .profile-picture-actions {
        flex: 1;
        min-width: 200px;
    }
    .profile-picture-input { display: none; }
    .profile-page .profile-picture-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }
    html body main .profile-page .app-page-btn,
    html body main .profile-page form button.app-page-btn,
    html body main .profile-page form button[type="submit"].app-page-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 0 !important;
        width: auto !important;
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 18px !important;
        border: 0 !important;
        border-radius: 8px !important;
        background: #b2cd16 !important;
        color: #0a1347 !important;
        font-family: inherit;
        font-size: 14px !important;
        font-weight: 700 !important;
        box-shadow: none !important;
        text-decoration: none !important;
    }
    html body main .profile-page .app-page-btn:hover,
    html body main .profile-page form button.app-page-btn:hover {
        background: #9eb812 !important;
        color: #0a1347 !important;
    }
    html body main .profile-page .app-page-btn--danger {
        background: #dc2626 !important;
        color: #fff !important;
    }
    .profile-page .app-form-hint {
        color: #6b7280;
        font-size: 13px;
        margin: 0;
    }
    .profile-flash {
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 14px;
        background: #dcfce7;
        color: #166534;
        font-weight: 600;
        font-size: 0.88rem;
    }
    @media (max-width: 768px) {
        .profile-page { padding: 16px 4px 24px; }
        html body main .profile-page h1 { font-size: 28px; }
        .profile-page .app-page-panel { padding: 16px 14px; }
        .profile-page .app-form-grid { grid-template-columns: 1fr; }
        html body main .profile-page .app-page-btn { width: 100% !important; }
    }
</style>

<div class="app-page profile-page">
    <h1>Profile</h1>
    <p class="app-page-lead">Update your password and profile picture. Name and email are managed by your senior manager or manager.</p>

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
                    <label for="current_password">Current security</label>
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

        <div class="app-page-panel profile-picture-panel">
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
                    <button type="submit" class="app-page-btn app-page-btn--primary">Save changes</button>
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
