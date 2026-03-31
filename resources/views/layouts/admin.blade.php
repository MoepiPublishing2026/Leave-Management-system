<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" sizes="32x32" />
    <link rel="icon" href="{{ asset('images/favicon-16.png') }}" type="image/png" sizes="16x16" />
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-512.png') }}" type="image/png" sizes="512x512" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}" sizes="180x180" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html, body {
            margin: 0; padding: 0; height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: white; color: #333;
            overflow-x: hidden;
        }

        /* Top navigation bar */
        header.navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 90px;
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 24px;
            z-index: 9999;
            border-bottom: 3px solid #a5d817;
            gap: 18px;
}
        

        header.navbar .logo img {
            height: 80px;
            width: auto;
        }

        nav.menu {
            display: flex;
            gap: 24px;
            align-items: center;
            justify-content: flex-start;
            margin-left: auto;
        }

        nav.menu a {
            color: #5e920aff;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        nav.menu a:hover,
        nav.menu a.active {
            background-color: #eff106;
            color: #0a1347;
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
            width: min(86vw, 320px);
            background: white;
            z-index: 10001;
            transform: translateX(-105%);
            transition: transform 0.25s ease;
            box-shadow: 8px 0 22px rgba(0,0,0,0.18);
            padding: 18px 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
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
            background: #eff106;
        }

        .navbar-profile {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-left: 20px;
            outline: none;
        }
        .profile-circle {
            width: 42px;
            height: 42px;
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
            top: 52px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            min-width: 140px;
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

        form#logout-form {
            display: none;
        }

        /* Main content below navbar */
        main.content {
            margin-top: 90px;
            padding: 24px;
            max-width: 1500px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 992px) {
            header.navbar {
                height: auto;
                padding: 10px 16px;
                flex-wrap: wrap;
                gap: 10px;
            }
            header.navbar .logo img {
                height: 70px;
            }
            nav.menu { display: none; }
            .mobile-menu-btn { display: inline-flex; align-items: center; justify-content: center; }
            main.content {
                padding: 18px;
                max-width: 100%;
            }
        }
    </style>

    @stack('styles')
    @yield('head')
</head>
<body>
    <header class="navbar" role="banner">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/logo_trimmed.png') }}" alt="Moepi Publishing Logo">
            </a>
        </div>

        <button class="mobile-menu-btn" type="button" aria-label="Open menu" onclick="toggleMobileSidebar(true)">&#9776;</button>

        @php
            $unreadMessageCount = 0;
            if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('leave_request_messages') && \Illuminate\Support\Facades\Schema::hasTable('leave_request_conversations')) {
                $meId = auth()->id();
                $unreadMessageCount = \Illuminate\Support\Facades\DB::table('leave_request_messages as m')
                    ->join('leave_request_conversations as c', 'c.id', '=', 'm.conversation_id')
                    ->where('c.admin_id', $meId)
                    ->where('m.sender_id', '!=', $meId)
                    ->whereNull('m.read_at')
                    ->count();
            }
        @endphp

        <nav class="menu" role="navigation" aria-label="Primary Navigation">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">Employees</a>
            <a href="{{ route('admin.requests') }}" class="{{ request()->routeIs('admin.requests') ? 'active' : '' }}">All Requests</a>
            <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
                Messages
                @if($unreadMessageCount > 0)
                    <span class="badge-notification">{{ $unreadMessageCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.calendar') }}" class="{{ request()->routeIs('admin.calendar') ? 'active' : '' }}">Calendar</a>
            <a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">Analytics</a>
            <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">Settings</a>
        </nav>
        @auth
            @php $user = auth()->user(); @endphp
            <div class="navbar-profile" id="profileMenu" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="User menu">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" class="profile-image" />
                @else
                    <div class="profile-circle" title="{{ $user->first_name }} {{ $user->last_name }}">
                        {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? 'S', 0, 1)) }}
                    </div>
                @endif
                <div class="profile-dropdown" id="profileDropdown" role="menu" aria-hidden="true">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                </div>
            </div>
            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
            </form>
        @endauth
    </header>

    <div id="mobileSidebarBackdrop" class="mobile-sidebar-backdrop" onclick="toggleMobileSidebar(false)"></div>
    <aside id="mobileSidebar" class="mobile-sidebar" aria-label="Mobile Navigation">
        <div class="mobile-sidebar-header">
            <div class="mobile-sidebar-title">Menu</div>
            <button class="mobile-menu-btn" type="button" aria-label="Close menu" onclick="toggleMobileSidebar(false)">&times;</button>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">Employees</a>
        <a href="{{ route('admin.requests') }}" class="{{ request()->routeIs('admin.requests') ? 'active' : '' }}">All Requests</a>
        <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <span>Messages</span>
            @if($unreadMessageCount > 0)
                <span class="badge-notification">{{ $unreadMessageCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.calendar') }}" class="{{ request()->routeIs('admin.calendar') ? 'active' : '' }}">Calendar</a>
        <a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">Analytics</a>
        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">Reports</a>
        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">Settings</a>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </aside>

    <main class="content" role="main">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
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

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                timer: 3500,
                timerProgressBar: true,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    @if (session('status'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('status')),
                timer: 3500,
                timerProgressBar: true,
                showConfirmButton: false
            });
        </script>
    @endif
    @if (session('saved'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Changes saved successfully.',
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Please fix the following errors:',
                html: '<ul style="text-align:left;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
                confirmButtonText: 'OK',
                width: '40em'
            });
        </script>
    @endif

    <script>
        function toggleMobileSidebar(open) {
            const sidebar = document.getElementById('mobileSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            if (!sidebar || !backdrop) return;
            if (open) {
                sidebar.classList.add('open');
                backdrop.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.remove('open');
                backdrop.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleMobileSidebar(false);
        });
    </script>
</body>
</html>
