@extends('layouts.storefront')

@section('content')
    {{-- Hero — cross-fading slides, driven by initHero() in app.js. Slide one
         carries .is-active so the hero renders fully without JavaScript. --}}
    <section data-hero class="relative overflow-hidden bg-cream-2">
        <div class="relative h-[520px] sm:h-[560px] lg:h-[640px]">
            @foreach($heroSlides as $slide)
                <div data-hero-slide class="absolute inset-0 {{ $loop->first ? 'is-active' : '' }}">
                    {{-- The photo fills the frame on small screens and takes the
                         right half from lg up, so portrait shots aren't cropped
                         to a sliver. --}}
                    <div class="absolute inset-0 lg:left-[46%]">
                        <img src="{{ $slide['img'] }}" alt="{{ $slide['title'] }} {{ $slide['accent'] }}"
                             @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                             class="h-full w-full object-cover object-center">
                        {{-- Softens the seam between the cream panel and the photo --}}
                        <div class="absolute inset-y-0 left-0 hidden w-32 bg-gradient-to-r from-cream-2 to-transparent lg:block"></div>
                    </div>
                    {{-- Scrim for the overlay layout below lg --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-cream-2 via-cream-2/85 to-cream-2/20 lg:hidden"></div>

                    <div class="relative flex h-full items-center px-8 md:px-16 lg:w-1/2">
                        <div class="flex max-w-[520px] flex-col gap-5">
                            <div class="text-[12px] font-medium tracking-[0.32em] text-blush-soft">{{ $slide['eyebrow'] }}</div>
                            <h1 class="text-[38px] font-light leading-[1.05] tracking-[0.01em] sm:text-[48px] lg:text-[64px]">
                                {{ $slide['title'] }}<br>
                                <span class="font-serif font-medium italic text-blush">{{ $slide['accent'] }}</span>
                            </h1>
                            <p class="max-w-[420px] text-[15.5px] font-light leading-[1.65] text-muted-2">{{ $slide['copy'] }}</p>
                            <div class="mt-1.5">
                                <a href="{{ $slide['href'] }}" class="tc-btn-dark">{{ $slide['cta'] }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Controls --}}
        <button type="button" data-hero-prev aria-label="Previous slide" class="tc-arrow absolute left-4 top-1/2 hidden -translate-y-1/2 md:flex">&lsaquo;</button>
        <button type="button" data-hero-next aria-label="Next slide" class="tc-arrow absolute right-4 top-1/2 hidden -translate-y-1/2 md:flex">&rsaquo;</button>

        <div class="absolute bottom-7 left-1/2 flex -translate-x-1/2 gap-2">
            @foreach($heroSlides as $slide)
                <button type="button" data-hero-dot aria-label="Go to slide {{ $loop->iteration }}"
                        class="h-2 w-2 rounded-full bg-ink/25 transition-all duration-300 {{ $loop->first ? 'is-active' : '' }}"></button>
            @endforeach
        </div>
    </section>

    {{-- Categories --}}
    @if($categories->isNotEmpty())
        <section class="pb-14 pt-14">
            <h2 class="tc-heading">Shop by Category</h2>
            <span class="tc-heading-rule"></span>
            <div data-carousel class="relative mt-9">
                <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-8 overflow-x-auto scroll-px-5 scroll-smooth px-5 md:scroll-px-10 md:px-10">
                    @foreach($categories as $c)
                        <a href="{{ route('listing', $c) }}" class="group flex w-[170px] shrink-0 snap-start flex-col items-center gap-3.5 sm:w-[200px]">
                            <div class="h-[170px] w-[170px] overflow-hidden rounded-full bg-cream sm:h-[200px] sm:w-[200px]">
                                @if($c->image_url)
                                    <img src="{{ $c->image_url }}" alt="{{ $c->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="text-center text-[16px] font-medium transition-colors group-hover:text-blush">{{ $c->name }}</div>
                            <div class="-mt-2.5 text-[13px] font-light text-muted">{{ $counts[$c->id] ?? 0 }} {{ Str::plural('product', $counts[$c->id] ?? 0) }}</div>
                        </a>
                    @endforeach
                </div>
                <button type="button" data-carousel-prev aria-label="Previous categories" class="tc-arrow absolute left-2 top-[38%] -translate-y-1/2 md:left-3">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next categories" class="tc-arrow absolute right-2 top-[38%] -translate-y-1/2 md:right-3">&rsaquo;</button>
            </div>
        </section>
    @endif

    {{-- Featured products --}}
    @if($featured->isNotEmpty())
        <section class="pb-14">
            <h2 class="tc-heading">Featured Products</h2>
            <span class="tc-heading-rule"></span>
            <div data-carousel class="relative mt-9">
                <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-px-5 scroll-smooth px-5 md:scroll-px-10 md:px-10">
                    @foreach($featured as $p)
                        <div class="w-[70%] shrink-0 snap-start sm:w-[46%] md:w-[31%] lg:w-[23.5%]">
                            @include('partials.product-card', ['p' => $p, 'h' => 'h-[340px]'])
                        </div>
                    @endforeach
                </div>
                <button type="button" data-carousel-prev aria-label="Previous products" class="tc-arrow absolute left-2 top-[35%] -translate-y-1/2 md:left-3">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next products" class="tc-arrow absolute right-2 top-[35%] -translate-y-1/2 md:right-3">&rsaquo;</button>
            </div>
        </section>
    @endif

    {{-- Promise band — scrolling marquee --}}
    <div class="tc-marquee overflow-hidden border-y border-line bg-cream-3 py-4">
        <div class="tc-marquee-track flex w-max items-center whitespace-nowrap">
            {{-- The list is rendered twice so the loop has no seam --}}
            @foreach([1, 2] as $pass)
                @foreach($marquee as $item)
                    <span class="flex items-center gap-3 px-8 text-[14.5px] font-light text-ink" @if($pass === 2) aria-hidden="true" @endif>
                        <span class="text-[13px] text-blush">★</span>{{ $item }}
                    </span>
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Promo banners — real categories, priced from live stock --}}
    <div class="flex flex-col gap-6 px-5 md:px-10 py-14 md:flex-row">
        @foreach($promos as $promo)
            <a href="{{ route('listing', $promo['category']) }}" class="group relative h-[260px] flex-1 overflow-hidden bg-[#e9ddd6]">
                @if($promo['category']->image_url)
                    <img src="{{ $promo['category']->image_url }}" alt="{{ $promo['category']->name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                @endif
                <div class="pointer-events-none absolute inset-0 flex flex-col justify-center gap-2 bg-gradient-to-r from-white/75 via-white/40 to-transparent px-5 md:px-10">
                    <div class="text-[12px] font-medium tracking-[0.24em]">{{ $promo['eyebrow'] }}</div>
                    @if($promo['from'])
                        <div class="text-[15px] font-light">Starting at <span class="text-[22px] font-semibold text-blush">{{ $promo['from'] }}</span></div>
                    @endif
                    <div class="text-[30px] font-normal leading-[1.2]">{{ $promo['category']->name }}</div>
                    <div class="mt-1.5 text-[14px] font-medium text-blush underline underline-offset-2">Shop Now</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Deal of the week --}}
    @if($deals->isNotEmpty())
        <section class="pb-14">
            <h2 class="tc-heading">Deal of the Week</h2>
            <span class="tc-heading-rule"></span>
            @if($countdown)
                {{-- Rendered server-side; the ticker in app.js counts it down. --}}
                <div class="mt-5 flex justify-center gap-2.5" data-countdown="{{ $dealEndsAt->toIso8601String() }}">
                    @foreach($countdown as $t)
                        <div class="w-16 bg-cream py-2.5 text-center">
                            <div class="text-[20px] font-semibold text-blush" data-countdown-part="{{ $t['k'] }}">{{ str_pad((string) $t['n'], 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-[11px] font-light tracking-[0.12em] text-muted">{{ $t['l'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div data-carousel class="relative mt-9">
                <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-px-5 scroll-smooth px-5 md:scroll-px-10 md:px-10">
                    @foreach($deals as $p)
                        <div class="w-[70%] shrink-0 snap-start sm:w-[46%] md:w-[31%] lg:w-[23.5%]">
                            @include('partials.product-card', ['p' => $p, 'h' => 'h-[340px]'])
                        </div>
                    @endforeach
                </div>
                <button type="button" data-carousel-prev aria-label="Previous deals" class="tc-arrow absolute left-2 top-[35%] -translate-y-1/2 md:left-3">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next deals" class="tc-arrow absolute right-2 top-[35%] -translate-y-1/2 md:right-3">&rsaquo;</button>
            </div>
        </section>
    @endif

    {{-- Testimonials --}}
    <section class="bg-cream px-5 py-14 md:px-10">
        <h2 class="tc-heading">What the Closet Says</h2>
        <span class="tc-heading-rule"></span>
        <div class="mx-auto mt-9 grid max-w-[1200px] gap-6 md:grid-cols-3">
            @foreach($testimonials as $t)
                <figure class="flex h-full flex-col gap-4 bg-white p-8">
                    <div class="text-[13px] tracking-[2px] text-gold">{{ str_repeat('★', $t['stars']) . str_repeat('☆', 5 - $t['stars']) }}</div>
                    <blockquote class="flex-1 font-serif text-[18px] italic leading-[1.6] text-muted-3">“{{ $t['quote'] }}”</blockquote>
                    <figcaption class="flex items-center gap-3 border-t border-line pt-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-tan text-[14px] font-medium text-ink">{{ Str::substr($t['name'], 0, 1) }}</span>
                        <span>
                            <span class="block text-[14.5px] font-medium">{{ $t['name'] }}</span>
                            <span class="block text-[12.5px] font-light text-muted">{{ $t['meta'] }}</span>
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>

@endsection
