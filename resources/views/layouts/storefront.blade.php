<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Trendy Closet by Leila Konsol')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,200;0,300;0,400;0,500;0,600;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Space+Grotesk:wght@400;500&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('images/logo-64.png') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-ink antialiased">
    @include('partials.header')
    @include('partials.flash')
    <main>
        @yield('content')
    </main>
    @include('partials.footer')
    @include('partials.whatsapp')
</body>
</html>
