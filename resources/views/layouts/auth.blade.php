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
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Space+Grotesk:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-ink antialiased">
<div class="flex min-h-screen">

    {{-- Editorial panel — decorative, hidden on small screens --}}
    <aside class="relative hidden w-[46%] shrink-0 overflow-hidden bg-ink-deep lg:block">
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=60&w=1400&auto=format&fit=crop"
             alt=""
             class="absolute inset-0 h-full w-full object-cover opacity-45">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-deep/70 via-ink-deep/40 to-ink-deep/85"></div>

        <div class="relative flex h-full flex-col justify-between px-14 py-12 text-white">
            <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2">
                <span class="font-serif text-[26px] leading-none">Trendy Closet</span>
                <span class="text-[10px] font-medium tracking-[0.22em] text-tan uppercase">Admin</span>
            </a>

            <div>
                <div class="mb-5 h-px w-14 bg-tan/50"></div>
                <p class="max-w-[24ch] font-serif text-[38px] leading-[1.15] font-normal">
                    The back room of the boutique.
                </p>
                <p class="mt-4 max-w-[38ch] text-[14.5px] leading-relaxed font-light text-white/65">
                    Manage the catalogue, orders, and customer enquiries for
                    Trendy Closet by Leila Konsol.
                </p>
            </div>

            <div class="text-[11px] tracking-[0.16em] text-white/40 uppercase">
                Authorised staff only
            </div>
        </div>
    </aside>

    {{-- Form panel --}}
    <main class="flex flex-1 flex-col px-8 py-10 md:px-16">
        <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2 lg:hidden">
            <span class="font-serif text-[24px] leading-none">Trendy Closet</span>
            <span class="text-[10px] font-medium tracking-[0.22em] text-blush uppercase">Admin</span>
        </a>

        <div class="flex flex-1 items-center justify-center py-12">
            <div class="w-full max-w-[400px]">
                <div class="mb-2 text-[11px] font-medium tracking-[0.22em] text-blush uppercase">@yield('eyebrow', 'Staff access')</div>
                <h1 class="font-serif text-[36px] leading-tight font-normal">@yield('heading')</h1>
                <p class="mt-2.5 text-[14.5px] leading-relaxed font-light text-muted-2">@yield('subheading')</p>

                @if (session('status'))
                    <div class="mt-7 border-l-2 border-jade bg-cream-3 px-4 py-3 text-[13.5px] leading-relaxed font-light text-muted-3">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-8">
                    @yield('form')
                </div>
            </div>
        </div>

        <div class="text-center text-[12.5px] font-light text-faint lg:text-left">
            <a href="{{ route('home') }}" class="transition-colors hover:text-blush">← Back to the storefront</a>
        </div>
    </main>
</div>
</body>
</html>
