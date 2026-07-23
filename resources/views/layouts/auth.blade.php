<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Back office — Trendy Closet')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('images/logo-64.png') }}" sizes="any">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ad-root min-h-screen bg-slate-100 text-slate-800 antialiased">
<div class="flex min-h-screen">

    {{-- Brand plate — pure black, matching the back-office rail. Decorative, so
         it only appears when there is room for it. --}}
    <aside class="relative hidden w-[46%] shrink-0 flex-col justify-between overflow-hidden bg-black px-14 py-14 lg:flex">
        {{-- faint dotted texture --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
             style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 22px 22px;"></div>

        <div class="relative flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-[16px] font-bold text-black">T</span>
            <span class="text-[15px] font-semibold tracking-[-0.01em] text-white">Trendy Closet</span>
        </div>

        <div class="relative">
            <span class="mb-6 block h-px w-12 bg-white/30"></span>
            <p class="max-w-[20ch] text-[38px] leading-[1.12] font-semibold tracking-[-0.02em] text-white">
                The room behind the shop floor.
            </p>
            <p class="mt-5 max-w-[42ch] text-[14px] leading-relaxed font-normal text-slate-400">
                Catalogue, orders and customer enquiries for Trendy Closet by Leila Konsol.
            </p>
        </div>

        <div class="relative text-[12px] font-normal text-slate-500">
            &copy; {{ now()->year }} Leila Konsol · Back office
        </div>
    </aside>

    {{-- Form panel --}}
    <main class="flex flex-1 flex-col justify-center px-6 py-12 sm:px-14">
        <div class="mx-auto w-full max-w-[380px]">

            {{-- Wordmark, only when the plate is hidden (small screens) --}}
            <a href="{{ route('home') }}" class="tc-auth-rise mb-9 flex items-center gap-2.5 lg:hidden">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-black text-[16px] font-bold text-white">T</span>
                <span class="text-[15px] font-semibold tracking-[-0.01em] text-slate-900">Trendy Closet</span>
            </a>

            <div class="tc-auth-rise [animation-delay:70ms]">
                <div class="ad-eyebrow">Back office</div>
                <h1 class="mt-2.5 text-[28px] leading-tight font-bold tracking-[-0.02em] text-slate-900">@yield('heading')</h1>
                <p class="mt-2.5 max-w-[40ch] text-[13.5px] leading-relaxed font-normal text-slate-500">@yield('subheading')</p>

                @if (session('status'))
                    <div class="ad-card mt-6 flex items-start gap-3 border-l-2 border-l-emerald-500 px-4 py-3">
                        <span class="mt-0.5 text-[13px] text-emerald-600">✓</span>
                        <p class="text-[13px] leading-relaxed font-normal text-slate-600">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="mt-7">
                    @yield('form')
                </div>
            </div>

            <p class="tc-auth-rise mt-9 text-[12.5px] font-normal text-slate-400 [animation-delay:150ms]">
                <a href="{{ route('home') }}" class="transition-colors hover:text-slate-700">← Back to the storefront</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>
