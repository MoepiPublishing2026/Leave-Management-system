<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Moepi Publishing')</title>
    @include('partials.letterhead-document-styles')
    @yield('styles')
</head>
<body class="lh-page-body">
    <div class="lh-sheet">
        <div class="lh-accent" aria-hidden="true"></div>
        @include('partials.letterhead-document-header')
        <div class="lh-body">
            @yield('content')
        </div>
        @include('partials.letterhead-document-footer')
    </div>
</body>
</html>
