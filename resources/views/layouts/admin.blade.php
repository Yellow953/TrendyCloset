<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Back office') — Trendy Closet</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('images/logo-64.png') }}" sizes="any">

    {{-- Apply the saved collapsed state before first paint, so a collapsed rail
         never flashes open on load. --}}
    <script>try{if(localStorage.getItem('ad-collapsed')==='1')document.documentElement.classList.add('ad-collapsed')}catch(e){}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ad-root min-h-screen bg-slate-100 text-slate-800 antialiased">

@include('partials.admin.sidebar')

{{-- Scrim, for when the sidebar is a slide-in panel below `lg` --}}
<div data-admin-scrim class="fixed inset-0 z-30 bg-black/45 lg:hidden"></div>

<div class="ad-main flex min-h-screen flex-col">
    @include('partials.admin.topbar')

    <main class="flex-1 px-5 py-7 md:px-8 md:py-9">
        <div class="mx-auto w-full max-w-[1400px]">

            {{-- Page heading. Pages fill `heading`; `actions` is the button rail. --}}
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    @hasSection('breadcrumb')
                        <div class="mb-2 flex items-center gap-1.5 text-[12px] font-medium text-slate-400">@yield('breadcrumb')</div>
                    @endif
                    <h1 class="text-[24px] leading-tight font-bold tracking-[-0.02em] text-slate-900 md:text-[27px]">@yield('heading')</h1>
                    @hasSection('subheading')
                        <p class="mt-1.5 text-[13.5px] font-normal text-slate-500">@yield('subheading')</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2.5">@yield('actions')</div>
            </div>

            @include('partials.admin.flash')

            @yield('content')
        </div>
    </main>

    @include('partials.admin.footer')
</div>

{{-- Modal host. Any [data-modal-open="id"] opens the [data-modal="id"] below;
     pages stack their own dialogs into this section. --}}
@yield('modals')

<div class="tc-toasts" data-toasts></div>
</body>
</html>
