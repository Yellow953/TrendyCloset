@extends('layouts.storefront')

@section('title', 'Leila Wrap Midi Dress — Sage · Trendy Closet')

@section('content')
    <div class="px-5 md:px-10 pb-0 pt-5 text-[13px] font-light text-muted">Home / Women / Dresses / <span class="text-ink">Leila Wrap Midi Dress</span></div>

    <div class="flex flex-col gap-11 px-5 md:px-10 pb-12 pt-6 lg:flex-row">
        {{-- Gallery --}}
        <div class="flex w-full flex-col-reverse gap-3.5 sm:flex-row lg:w-auto lg:flex-none">
            <div class="flex flex-row gap-3 sm:flex-col">
                @foreach($gallery as $g)
                    <div class="h-[80px] w-[68px] flex-none overflow-hidden bg-cream ring-1 ring-transparent transition hover:ring-blush sm:h-[104px] sm:w-[88px]"><img src="{{ $g['img'] }}" alt="Product view" class="h-full w-full object-cover"></div>
                @endforeach
            </div>
            <div class="relative h-[420px] w-full overflow-hidden bg-cream sm:h-[560px] sm:max-w-[460px] lg:w-[460px]">
                <img src="{{ $main['img'] }}" alt="Leila Wrap Midi Dress" class="h-full w-full object-cover">
                <div class="pointer-events-none absolute left-3.5 top-3.5 bg-blush px-2.5 py-1 text-[12px] font-medium text-white">-15%</div>
            </div>
        </div>

        {{-- Info --}}
        <div class="flex flex-1 flex-col gap-4">
            <h1 class="text-[32px] font-normal leading-[1.2]">Leila Wrap Midi Dress — Sage</h1>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-[14px] tracking-[2px] text-gold">★★★★★</span>
                <span class="text-[13px] font-light text-muted">4.9 · 36 reviews</span>
                <span class="text-[13px] font-light text-jade">✓ In stock</span>
            </div>
            <div><span class="text-[18px] font-light text-faint line-through">$64.00</span> <span class="text-[28px] font-semibold text-blush">$54.00</span></div>
            <p class="max-w-[440px] text-[14.5px] font-light leading-[1.65] text-muted-3">A soft wrap silhouette in breathable viscose, cut to flatter every shape. True to size — Leila wears an S. Ties at the waist, midi length, side pockets.</p>

            <div>
                <div class="mb-2 text-[13px] font-medium tracking-[0.08em]">COLOR — SAGE</div>
                <div class="flex gap-2.5">
                    <div class="h-[26px] w-[26px] rounded-full bg-[#8a9a8e] outline-2 outline-offset-2 outline-blush"></div>
                    <div class="h-[26px] w-[26px] rounded-full bg-tan"></div>
                    <div class="h-[26px] w-[26px] rounded-full bg-ink"></div>
                </div>
            </div>

            <div>
                <div class="mb-2 flex max-w-[440px] justify-between"><span class="text-[13px] font-medium tracking-[0.08em]">SIZE</span><span class="text-[13px] font-light text-blush underline underline-offset-2">Size guide</span></div>
                <div class="flex flex-wrap gap-2">
                    @foreach($sizes as $s)
                        <div class="w-[52px] border border-line-2 py-2.5 text-center text-[14px] font-normal transition-colors hover:border-blush hover:text-blush">{{ $s }}</div>
                    @endforeach
                </div>
            </div>

            <div class="mt-1.5 flex flex-wrap gap-3">
                <div class="flex items-center border border-line-2">
                    <div class="px-[18px] py-3.5 text-[16px]">−</div><div class="px-2.5 py-3.5 text-[15px] font-medium">1</div><div class="px-[18px] py-3.5 text-[16px]">+</div>
                </div>
                <button class="bg-ink px-11 py-3.5 text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Add to Bag — $54.00</button>
                <div class="border border-line-2 px-[18px] py-3.5 text-[16px] text-blush">♡</div>
            </div>

            <div class="flex max-w-[440px] flex-wrap gap-6 border-t border-line pt-4 text-[13px] font-light text-muted-2">
                <span>🚚 Free shipping over $150</span><span>↩ 30-day returns</span><span>🔒 Secure checkout</span>
            </div>

            <div class="max-w-[440px]">
                <div class="flex gap-6 border-b border-line text-[13.5px] font-medium tracking-[0.06em]">
                    <div class="border-b-2 border-blush py-2.5 text-blush">DETAILS</div>
                    <div class="py-2.5 text-muted">FABRIC &amp; CARE</div>
                    <div class="py-2.5 text-muted">REVIEWS (36)</div>
                </div>
                <div class="pt-3 text-[14px] font-light leading-[1.7] text-muted-3">Wrap front with self-tie belt · V-neckline · Midi length, 118 cm · Side seam pockets · Model is 172 cm wearing size S.</div>
            </div>
        </div>
    </div>

    {{-- Related --}}
    <div class="px-5 md:px-10 pb-12">
        <h2 class="mb-5 text-[26px] font-normal">You may also like</h2>
        <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
            @foreach($related as $p)
                <a href="{{ route('product') }}" class="group block">
                    <div class="h-[260px] overflow-hidden bg-cream"><img src="{{ $p['img'] }}" alt="{{ $p['name'] }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"></div>
                    <div class="mt-3 text-[15px] font-normal leading-[1.4]">{{ $p['name'] }}</div>
                    <div class="mt-1 text-[15px] font-semibold text-blush">{{ $p['now'] }}</div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
