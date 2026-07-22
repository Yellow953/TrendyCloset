<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Admin — Trendy Closet')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,200;0,300;0,400;0,500;0,600;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Space+Grotesk:wght@400;500&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('images/logo-64.png') }}" sizes="any">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-ink antialiased">
<div class="flex min-h-screen">

    {{-- Editorial plate — decorative, so it only appears when there is room for it --}}
    <aside class="tc-auth-plate relative hidden w-1/2 shrink-0 overflow-hidden bg-ink-deep lg:block">
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=70&w=1600&auto=format&fit=crop"
             alt=""
             class="absolute inset-0 h-full w-full object-cover opacity-50">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-deep/55 via-ink-deep/35 to-ink-deep/90"></div>

        <div class="relative flex h-full flex-col justify-end px-16 py-16 text-white">
            <span class="mb-6 block h-px w-12 bg-tan/60"></span>
            <p class="max-w-[18ch] font-serif text-[42px] leading-[1.12] font-normal">
                The room behind the shop floor.
            </p>
            <p class="mt-5 max-w-[40ch] text-[14px] leading-relaxed font-light text-white/60">
                Catalogue, orders and customer enquiries for Trendy Closet by Leila Konsol.
            </p>
        </div>
    </aside>

    {{-- Form panel --}}
    <main class="flex flex-1 flex-col justify-center px-7 py-12 sm:px-14">
        <div class="mx-auto w-full max-w-[400px]">

            <a href="{{ route('home') }}" class="tc-auth-rise mb-10 block">
                <span class="tc-wordmark block text-[18px] leading-none">Trendy Closet</span>
                <span class="mt-2.5 block text-[10px] font-medium tracking-[0.3em] text-blush uppercase">Back office</span>
            </a>

            <div class="tc-auth-rise [animation-delay:90ms]">
                <h1 class="font-serif text-[34px] leading-tight font-normal">@yield('heading')</h1>
                <span class="mt-4 block h-px w-10 bg-brand-deep"></span>
                <p class="mt-4 max-w-[38ch] text-[14px] leading-relaxed font-light text-muted-2">@yield('subheading')</p>

                @if (session('status'))
                    <div class="mt-7 border-l-2 border-jade bg-cream-3 px-4 py-3 text-[13.5px] leading-relaxed font-light text-muted-3">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-8">
                    @yield('form')
                </div>
            </div>

            <p class="tc-auth-rise mt-10 text-[12.5px] font-light text-faint [animation-delay:180ms]">
                <a href="{{ route('home') }}" class="transition-colors hover:text-blush">← Back to the storefront</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>
