@extends('layouts.storefront')

@section('title', 'Trendy Closet — Curated fashion for the whole family')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-cream-2 px-8 py-16 md:px-16">
        <div class="absolute -right-20 -top-20 h-[340px] w-[340px] rounded-full bg-tan"></div>
        <div class="relative grid items-center gap-14 lg:grid-cols-[1fr_560px]">
            <div class="flex flex-col gap-5">
                <div class="text-[12px] font-medium tracking-[0.32em] text-blush-soft">TRENDY CLOSET · SUMMER 2026</div>
                <h1 class="text-[34px] font-light leading-[1.08] tracking-[0.01em] sm:text-[42px] md:text-[62px]">Wear the pieces<br><span class="font-serif text-[36px] font-medium italic text-blush sm:text-[46px] md:text-[66px]">everyone asks about</span></h1>
                <p class="max-w-[400px] text-[16px] font-light leading-[1.6] text-muted-2">Hand-picked for women, men and kids — straight from Leila's feed to your closet. New drops every Friday.</p>
                <div class="mt-1.5 flex flex-wrap gap-3.5">
                    <a href="{{ route('listing') }}" class="tc-btn-dark">Shop New In</a>
                    <a href="{{ route('about') }}" class="tc-btn-outline">Follow @trendycloset</a>
                </div>
                <div class="mt-3.5 flex gap-9">
                    <div><div class="text-[22px] font-semibold">24k</div><div class="text-[12.5px] font-light text-blush-soft">followers</div></div>
                    <div><div class="text-[22px] font-semibold">120+</div><div class="text-[12.5px] font-light text-blush-soft">curated pieces</div></div>
                    <div><div class="text-[22px] font-semibold">4.9★</div><div class="text-[12.5px] font-light text-blush-soft">store rating</div></div>
                </div>
            </div>
            <div class="relative hidden h-[520px] lg:block">
                <div class="absolute right-0 top-0 h-[520px] w-[420px] overflow-hidden"><img src="{{ $hero['img'] }}" alt="Trendy Closet summer look" class="h-full w-full object-cover"></div>
                <div class="absolute bottom-9 left-0 h-[240px] w-[200px] border-[6px] border-white shadow-[0_18px_40px_rgba(43,37,35,.18)]"><img src="{{ $heroDetail['img'] }}" alt="Styling detail" class="h-full w-full object-cover"></div>
                <div class="absolute right-[390px] top-[26px] z-[2] flex h-24 w-24 items-center justify-center rounded-full bg-ink text-center text-[11px] font-normal leading-[1.4] tracking-[0.08em] text-white">NEW DROP<br>FRIDAY</div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <div class="flex flex-wrap justify-between gap-6 border-b border-line px-5 md:px-10 py-[22px]">
        @foreach($services as $s)
            <div class="flex items-center gap-3">
                <div class="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-cream text-[15px] text-blush">{{ $s['icon'] }}</div>
                <div><div class="text-[14px] font-medium">{{ $s['title'] }}</div><div class="text-[12.5px] font-light text-muted">{{ $s['sub'] }}</div></div>
            </div>
        @endforeach
    </div>

    {{-- Categories --}}
    <section data-carousel class="pb-12 pt-12">
        <div class="mb-5 flex items-end justify-between px-5 md:px-10">
            <h2 class="text-[30px] font-normal">Shop by Category</h2>
            <div class="flex gap-2">
                <button type="button" data-carousel-prev aria-label="Previous categories" class="tc-arrow">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next categories" class="tc-arrow">&rsaquo;</button>
            </div>
        </div>
        <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-7 overflow-x-auto scroll-px-5 scroll-smooth px-5 md:scroll-px-10 md:px-10">
            @foreach($categories as $c)
                <a href="{{ route('listing') }}" class="group flex w-[136px] shrink-0 snap-start flex-col items-center gap-3 sm:w-[150px]">
                    <div class="h-[136px] w-[136px] overflow-hidden rounded-full sm:h-[150px] sm:w-[150px]"><img src="{{ $c['img'] }}" alt="{{ $c['name'] }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"></div>
                    <div class="text-[15px] font-medium">{{ $c['name'] }}</div>
                    <div class="-mt-2 text-[12.5px] font-light text-muted">{{ $c['count'] }}</div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured products --}}
    <section data-carousel class="pb-12 pt-2">
        <div class="mb-5 flex items-end justify-between px-5 md:px-10">
            <h2 class="text-[30px] font-normal">Featured Products</h2>
            <div class="flex gap-2">
                <button type="button" data-carousel-prev aria-label="Previous products" class="tc-arrow">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next products" class="tc-arrow">&rsaquo;</button>
            </div>
        </div>
        <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-px-5 scroll-smooth px-5 md:scroll-px-10 md:px-10">
            @foreach($featured as $p)
                <div class="w-[70%] shrink-0 snap-start sm:w-[46%] md:w-[31%] lg:w-[23.5%]">
                    @include('partials.product-card', ['p' => $p, 'h' => 'h-[300px]'])
                </div>
            @endforeach
        </div>
    </section>

    {{-- Promo banners --}}
    <div class="flex flex-col gap-6 px-5 md:px-10 pb-12 md:flex-row">
        <div class="relative h-[260px] flex-1 overflow-hidden bg-[#e9ddd6]">
            <img src="{{ $promo1['img'] }}" alt="Summer dresses for women" class="absolute inset-0 h-full w-full object-cover">
            <div class="pointer-events-none absolute inset-0 flex flex-col justify-center gap-2 px-5 md:px-10">
                <div class="text-[12px] font-medium tracking-[0.24em]">BEST COLLECTION</div>
                <div class="text-[15px] font-light">Starting at <span class="text-[22px] font-semibold text-blush">$29.00</span></div>
                <div class="text-[30px] font-normal leading-[1.2]">Summer Dresses<br>for Women</div>
                <div class="mt-1.5 text-[14px] font-medium text-blush underline underline-offset-2">Shop Now</div>
            </div>
        </div>
        <div class="relative h-[260px] flex-1 overflow-hidden bg-tan">
            <img src="{{ $promo2['img'] }}" alt="Playful looks for kids" class="absolute inset-0 h-full w-full object-cover">
            <div class="pointer-events-none absolute inset-0 flex flex-col justify-center gap-2 px-5 md:px-10">
                <div class="text-[12px] font-medium tracking-[0.24em]">BEST COLLECTION</div>
                <div class="text-[15px] font-light">Starting at <span class="text-[22px] font-semibold text-blush">$19.00</span></div>
                <div class="text-[30px] font-normal leading-[1.2]">Playful Looks<br>for Kids</div>
                <div class="mt-1.5 text-[14px] font-medium text-blush underline underline-offset-2">Shop Now</div>
            </div>
        </div>
    </div>

    {{-- Deal of the week --}}
    <section data-carousel class="pb-12 pt-2">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 px-5 md:px-10">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                <h2 class="text-[30px] font-normal">Deal of the Week</h2>
                <div class="flex gap-2.5">
                    @foreach($countdown as $t)
                        <div class="w-16 bg-cream py-2.5 text-center"><div class="text-[20px] font-semibold text-blush">{{ $t['n'] }}</div><div class="text-[11px] font-light tracking-[0.12em] text-muted">{{ $t['l'] }}</div></div>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" data-carousel-prev aria-label="Previous deals" class="tc-arrow">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next deals" class="tc-arrow">&rsaquo;</button>
            </div>
        </div>
        <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-px-5 scroll-smooth px-5 md:scroll-px-10 md:px-10">
            @foreach($deals as $p)
                <div class="w-[70%] shrink-0 snap-start sm:w-[46%] md:w-[31%] lg:w-[23.5%]">
                    @include('partials.product-card', ['p' => $p, 'h' => 'h-[300px]'])
                </div>
            @endforeach
        </div>
    </section>

    {{-- Instagram --}}
    <div class="flex flex-col items-center gap-8 border-t border-line px-5 md:px-10 py-10 md:flex-row">
        <div class="flex-1">
            <div class="text-[26px] font-normal">@trendycloset.byleilakonsol</div>
            <div class="mt-1.5 text-[14px] font-light text-muted">Daily styling, try-ons and first looks on Instagram.</div>
            <a href="{{ route('about') }}" class="mt-2.5 inline-block text-[13.5px] font-medium text-blush underline underline-offset-2">Follow us</a>
        </div>
        <div class="grid w-full grid-cols-4 gap-2 sm:w-auto sm:gap-3">
            @foreach($instagram as $ig)
                <div class="aspect-square overflow-hidden sm:h-[120px] sm:w-[120px]"><img src="{{ $ig['img'] }}" alt="Instagram post" class="h-full w-full object-cover"></div>
            @endforeach
        </div>
    </div>
@endsection
