<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo')

    {{-- Every photograph on the site comes from here, and the first one is
         needed immediately — pay the DNS/TLS cost up front rather than when the
         parser reaches the hero. --}}
    <link rel="preconnect" href="https://images.unsplash.com">

    {{-- Fonts are self-hosted (see vite.config.js): the @font-face rules are
         inlined and the body faces preloaded from our own origin, so the
         critical path holds no third-party stylesheet at all. Space Grotesk is
         asked for by the one page that uses it. --}}
    {{ Vite::fonts(['jost', 'cormorant-garamond']) }}

    <link rel="icon" href="{{ asset('images/logo-64.png') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Pages push their LCP preload (and anything else they alone need) here. --}}
    @stack('head')

    {{-- Scroll reveal only hides what it is about to animate in when there is
         JavaScript to bring it back. Set here, before first paint, so nothing
         flashes and a JS-off page is simply the page. --}}
    <script>document.documentElement.classList.add('tc-js');</script>
</head>
<body class="min-h-screen bg-white text-ink antialiased">
    @include('partials.header')
    @include('partials.flash')
    <main>
        @yield('content')
    </main>
    @include('partials.footer')
    @include('partials.whatsapp')
    @include('partials.drawer')
</body>
</html>
