<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Leave Management System')</title>
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
        html, body {
            margin: 0; padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: white; color: #333;
        }

        /* Navbar */
        header.navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 90px;
            background-color: #ffffff !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap;
            padding: 0 20px;
            box-shadow: 0 0 10px #bdb6b6ff;
            border-bottom: 3px solid #a5d817;
            z-index: 10000;
            user-select: none;
        }

        .navbar-logo {
            font-weight: 700;
            font-size: 22px;
            color: #4353ff;
            cursor: pointer;
            letter-spacing: 1.2px;
            display: flex;
            align-items: center;
        }
        .navbar-logo:hover,
        .navbar-logo:focus {
            background: transparent !important;
        }
        .navbar-logo img {
            height: 70px;
            width: auto;
            max-width: 280px;
            object-fit: contain;
            vertical-align: middle;
        }

        /* Navigation links */
        nav.menu {
            display: flex;
            gap: 6px;
            align-items: center;
            margin-left: auto;
        }
        nav.menu a {
            font-weight: 700;
            color: #5e920aff;
            text-decoration: none;
            padding: 9px 10px;
            border-radius: 8px;
            background-color: transparent;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        nav.menu a.active, nav.menu a:hover {
            background-color: #eff106;
            color: #0a1347;
        }
        /* Ensure hover wins if other CSS overrides it */
        nav.menu a:hover {
            background-color: #eff106 !important;
            color: #0a1347 !important;
        }

        /* Notification dropdown */
        .nav-item.dropdown {
            position: relative;
            display: flex;
            align-items: center;
        }
        .nav-item.dropdown > a {
            font-weight: 700;
            color: #5e920aff;
            text-decoration: none;
            padding: 9px 10px;
            border-radius: 8px;
            background-color: transparent;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 13px;
            display: flex;
            align-items: center;
        }
        .nav-item.dropdown > a:hover,
        .nav-item.dropdown > a:focus {
            background-color: #eff106 !important;
            color: #0a1347 !important;
        }

        .badge-notification {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 6px;
            border-radius: 999px;
            background: #4c8615ff;
            color: white;
            font-size: 11px;
            font-weight: 800;
            margin-left: 6px;
            line-height: 1;
        }
        .mobile-menu-btn {
            display: none;
            border: none;
            background: transparent;
            font-size: 26px;
            font-weight: 900;
            color: #0a1347;
            padding: 8px 10px;
            border-radius: 10px;
            cursor: pointer;
        }
        .mobile-menu-btn:hover {
            background: #f3f4f6;
        }
        .mobile-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 10000;
        }
        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            height: 100dvh;
            width: min(86vw, 320px);
            background: white;
            z-index: 10001;
            transform: translateX(-105%);
            transition: transform 0.25s ease;
            box-shadow: 8px 0 22px rgba(0,0,0,0.18);
            padding: 18px 16px;
            padding-bottom: calc(18px + env(safe-area-inset-bottom));
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        .mobile-sidebar > a {
            flex-shrink: 0;
        }
        .mobile-sidebar.open {
            transform: translateX(0);
        }
        .mobile-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
        }
        .mobile-sidebar-title {
            font-weight: 900;
            font-size: 16px;
            color: #0a1347;
        }
        .mobile-sidebar a {
            text-decoration: none;
            color: #0a1347;
            font-weight: 800;
            padding: 12px 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .mobile-sidebar a.active,
        .mobile-sidebar a:hover {
            background: #eff106 !important;
        }
        /* Don't apply link hover background to the sidebar logo */
        .mobile-sidebar a.mobile-sidebar-brand:hover,
        .mobile-sidebar a.mobile-sidebar-brand:focus {
            background: transparent !important;
        }

        .dropdown-menu.notifications-dropdown {
            min-width: 420px;
            max-height: 350px;
            overflow-y: auto;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .dropdown-menu.notifications-dropdown .dropdown-header {
            font-weight: 700;
            text-align: center;
            padding: 12px;
            border-bottom: 1px solid #eee;
            background: #ffffff;
        }
        .dropdown-menu.notifications-dropdown .dropdown-item {
            white-space: normal;
        }

        /* Profile Icon */
        .navbar-profile {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-left: 20px;
        }
        .profile-circle {
            width: 42px; height: 42px;
            background-color: #a5d817;
            border-radius: 50%;
            color: white;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            user-select: none;
        }
        .profile-image {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            user-select: none;
        }
        .profile-dropdown {
            position: absolute;
            top: 52px; right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            min-width: 200px;
            display: none;
            flex-direction: column;
            z-index: 1000;
        }
        .profile-dropdown.show {
            display: flex;
        }
        .profile-dropdown a {
            padding: 12px 18px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }
        .profile-dropdown a:last-child {
            border-bottom: none;
        }
        .profile-dropdown a:hover {
            background-color: #f0f2f7;
        }

        /* Main content below navbar — scroll offset handled by ui-consistency.css */
        main,
        main.content {
            padding: 40px;
            max-width: 1800px;
            margin-left: auto;
            margin-right: auto;
            background-color: #ffffff;
        }

        @media (max-width: 1200px) {
            header.navbar {
                height: 64px;
                padding: 8px 16px;
                flex-wrap: nowrap;
                gap: 10px;
            }
            .navbar-logo img {
                height: 40px;
                max-width: 200px;
            }
            /* Mobile navbar: hamburger left, small logo right (profile hidden) */
            .navbar-logo {
                display: flex;
                margin-left: auto;
                cursor: pointer;
            }
            .navbar-logo img { height: 40px; max-width: 200px; }
            .navbar-profile { display: none; }
            nav.menu { display: none; }
            .mobile-menu-btn { display: inline-flex; align-items: center; justify-content: center; }
            .mobile-menu-btn { margin-right: auto; }
            main,
            main.content {
                padding: 18px;
            }
            .dropdown-menu.notifications-dropdown {
                min-width: min(92vw, 440px);
            }
        }

        /* Mobile sidebar header: logo left, close right */
        .mobile-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .mobile-sidebar-brand {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        .mobile-sidebar-brand img {
            height: 46px;
            width: auto;
            max-width: 100%;
        }

        .mobile-sidebar-profile-btn {
            border: none;
            background: transparent;
            padding: 0;
            border-radius: 999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-sidebar-profile-btn:focus { outline: 2px solid rgba(178, 205, 22, 0.35); outline-offset: 3px; }
        .mobile-sidebar-profile-btn .profile-circle,
        .mobile-sidebar-profile-btn .profile-image {
            width: 38px;
            height: 38px;
        }
        /* remove link underline and colour */
        html body header.navbar .navbar-logo,
        html body header.navbar .navbar-logo:hover { text-decoration:none !important; color:#fff !important; }
        html body header.navbar .logo-text { color:#fff !important; text-decoration:none !important; }
        html body header.navbar .navbar-logo { gap:6px !important; letter-spacing:0 !important; }
        html body header.navbar .logo-text { margin-left:0 !important; }
        
        /* ===== Prototype match: notifications dropdown ===== */
        html body .dropdown-menu.notifications-dropdown {
            min-width: 360px;
            max-width: 360px;
            max-height: 240px;
            border: 0;
            border-radius: 10px;
            padding: 0;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.2);
            scrollbar-width: thin;
            scrollbar-color: #b8c2c2 #eef2f2;
        }
        html body .dropdown-menu.notifications-dropdown .dropdown-header {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            padding: 8px 12px;
            border-bottom: 1px solid #9aa5a5;
            background: #fff;
        }
        html body .dropdown-menu.notifications-dropdown .notification-dropdown-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 8px 12px;
            background: #fff;
        }
        html body .dropdown-menu.notifications-dropdown .notification-dropdown-item:hover {
            background: rgba(212, 225, 24, 0.15);
        }
        html body .dropdown-menu.notifications-dropdown .notification-dropdown-message {
            flex: 1 1 auto;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.3;
            color: #8a9a12;
        }
        html body .dropdown-menu.notifications-dropdown .notification-dropdown-time {
            flex: 0 0 auto;
            font-size: 11px;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }
        html body .dropdown-menu.notifications-dropdown .dropdown-divider {
            margin: 0;
            border-top: 1px solid #9aa5a5;
            opacity: 0.6;
        }

        #notificationsDropdown::after,
        #notificationsDropdown.dropdown-toggle::after {
            display: inline-block !important;
            margin-left: 6px !important;
            content: "" !important;
            border-top: 0.35em solid #fff !important;
            border-right: 0.32em solid transparent !important;
            border-bottom: 0 !important;
            border-left: 0.32em solid transparent !important;
        }
    </style>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profile = document.getElementById('profileMenu');
            const dropdown = document.getElementById('profileDropdown');

            if (profile && dropdown) {
                profile.addEventListener('click', () => {
                    dropdown.classList.toggle('show');
                });

                window.addEventListener('click', (e) => {
                    if (!profile.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>

    @include('partials.filter-select-styles')
    @include('partials.sweetalert-guard')
    @stack('styles')
    @include('partials.dashboard-status-card-styles')
    @yield('head')
    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}?v=20261106dashbtn"/>
</head>
<body class="role-manager">
    <header class="navbar" role="banner">
        <button class="mobile-menu-btn" type="button" aria-label="Open menu" onclick="toggleMobileSidebar(true)">&#9776;</button>

        <a class="navbar-logo" href="{{ route('manager.dashboard') }}" aria-label="Go to dashboard">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
            <span class="logo-text">MOEPI</span>
        </a>

        @php
            $unreadMessageCount = auth()->check()
                ? \App\Support\LeaveRequestChat::unreadCountFor(auth()->user())
                : 0;
            $notificationCount = 0;
            if (auth()->check()) {
                $notificationCount = \App\Support\UserNotifications::unreadCount(auth()->user());
            }
        @endphp

        <nav class="menu" aria-label="Primary Navigation">
            @include('partials.nav-links-manager')
        </nav>

            @auth
            @php
                $user = auth()->user();
                $notifications = \App\Support\UserNotifications::unreadForDropdown($user, 10);
            @endphp

        <div class="navbar-utilities">
            <div class="nav-item dropdown">
                <a href="#" id="notificationsDropdown" class="nav-link position-relative dropdown-toggle"
                   role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#5e920a" viewBox="0 0 24 24">
                        <path d="M12 2C8.13401 2 5 5.13401 5 9V16L3.29289 17.7071C2.90237 18.0976 3.02331 18.7069 3.53033 18.8944C3.73765 18.9712 3.96214 19 4.18534 19H19.8147C20.0379 19 20.2624 18.9712 20.4697 18.8944C20.9767 18.7069 21.0976 18.0976 20.7071 17.7071L19 16V9C19 5.13401 15.866 2 12 2ZM7 9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V17H7V9ZM12 22C13.1046 22 14 21.1046 14 20H10C10 21.1046 10.8954 22 12 22Z"/>
                    </svg>

                    @if($notificationCount > 0)
                        <span class="badge-notification">{{ $notificationCount }}</span>
                    @endif
                </a>

                @include('partials.navbar-notifications-dropdown')
            </div>

            <div class="navbar-profile" id="profileMenu" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="User menu">
                @if($user->profile_picture)
                    <img src="{{ route('users.profile_picture', ['user' => $user->id]) }}?v={{ optional($user->updated_at)->timestamp }}" alt="Profile Picture" class="profile-image" />
                @else
                    <div class="profile-circle" title="{{ $user->first_name }} {{ $user->last_name }}">
                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                    </div>
                @endif

                <div class="profile-dropdown" id="profileDropdown" role="menu" aria-hidden="true">
                    @include('partials.external-app-links')
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                       Logout
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
                        @csrf
                    </form>
                </div>
            </div>
            @endauth
        </nav>
    </header>

    <div id="mobileSidebarBackdrop" class="mobile-sidebar-backdrop" onclick="toggleMobileSidebar(false)"></div>
    <aside id="mobileSidebar" class="mobile-sidebar" aria-label="Mobile Navigation">
        <div class="mobile-sidebar-header">
            <a class="mobile-sidebar-brand" href="{{ route('manager.dashboard') }}" aria-label="Go to dashboard">
                <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
                <span class="logo-text">MOEPI</span>
            </a>
            @auth
                @php $user = auth()->user(); @endphp
                <button class="mobile-sidebar-profile-btn" type="button" aria-label="Close menu" onclick="toggleMobileSidebar(false)">
                    @if($user && $user->profile_picture)
                        <img src="{{ route('users.profile_picture', ['user' => $user->id]) }}?v={{ optional($user->updated_at)->timestamp }}" alt="Profile Picture" class="profile-image" />
                    @else
                        <div class="profile-circle" title="{{ $user->first_name }} {{ $user->last_name }}">
                            {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </button>
            @endauth
        </div>
        @include('partials.nav-links-manager', ['includeNotifications' => true])
        @include('partials.external-app-links')
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </aside>

    <main class="content" role="main">
        @yield('content')
    </main>
    @include('partials.site-footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleMobileSidebar(open) {
            const sidebar = document.getElementById('mobileSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            if (!sidebar || !backdrop) return;
            if (open) {
                sidebar.classList.add('open');
                backdrop.style.display = 'block';
                if (typeof window.lockAppShellScroll === 'function') {
                    window.lockAppShellScroll(true);
                } else {
                    document.body.style.overflow = 'hidden';
                }
            } else {
                sidebar.classList.remove('open');
                backdrop.style.display = 'none';
                if (typeof window.lockAppShellScroll === 'function') {
                    window.lockAppShellScroll(false);
                } else {
                    document.body.style.overflow = '';
                }
            }

        }
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleMobileSidebar(false);
        });
    </script>

    @include('partials.session-flash-swal')
    @stack('scripts')
    @include('partials.layout-scroll')
</body>
</html>
