<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'Moepi Publishing')</title>
    @include('partials.pdf-letterhead-styles')
    @yield('styles')
</head>
<body>
    @include('partials.pdf-letterhead')
    <div class="pdf-content">
        @yield('content')
    </div>
</body>
</html>
