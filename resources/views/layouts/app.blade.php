<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title') - GSD Estimator</title>

    @stack('styles')

    @vite(['resources/js/app.js'])
</head>

<body class="layout-3">
    <div id="app">
        <div class="main-wrapper container">
            @include('components.header')

            @yield('main')

            @include('components.footer')
        </div>
    </div>

    @stack('scripts')
</body>

</html>