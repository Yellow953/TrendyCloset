@extends('layouts.storefront')

@section('content')
    @php($contact = config('store.contact'))

    {{-- Hero --}}
    <div class="relative h-[340px] overflow-hidden bg-tan">
        <x-img :src="$hero['img']" alt="Trendy Closet studio" eager sizes="100vw"
               class="absolute inset-0 h-full w-full object-cover" />
        {{-- Scrim keeps the copy readable whatever the photo does --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-cream-2/85 via-cream-2/45 to-transparent"></div>
        <div class="pointer-events-none absolute inset-0 flex flex-col justify-center px-8 md:px-16">
            <div class="text-[12px] font-medium tracking-[0.28em] text-blush-soft">OUR STORY</div>
            {{-- The page's <h1>: it was a <div>, which left About with no
                 top-level heading at all. --}}
            <h1 class="mt-2.5 text-[34px] font-light leading-[1.15] text-ink md:text-[46px]">Your closet,<br><span class="font-serif font-medium italic text-blush">your style, your trend</span></h1>
        </div>
    </div>

    {{-- Story --}}
    <div class="flex flex-col items-center gap-14 px-8 py-14 md:px-16 lg:flex-row">
        <div class="h-[380px] w-full flex-1 overflow-hidden rounded-panel">
            <x-img :src="$portrait['img']" alt="Inside the Trendy Closet boutique" sizes="(min-width: 1024px) 45vw, 100vw"
                   class="h-full w-full object-cover" />
        </div>
        <div class="flex flex-1 flex-col gap-4">
            <h2 class="text-[30px] font-normal">Hi, I'm Pamela</h2>
            <p class="text-[15px] font-light leading-[1.75] text-muted-3">At Trendy Closet, fashion is more than what you wear — it's a way to express who you are. We're a family-owned boutique in Dekwaneh, Lebanon, for women who love discovering fresh styles, quality pieces and effortless looks at affordable prices.</p>
            <p class="text-[15px] font-light leading-[1.75] text-muted-3">Your wardrobe should always feel exciting. That's why new arrivals land every week — the latest fashion wear and denim, carefully selected to keep your style fresh, trendy and uniquely yours.</p>
            <div class="mt-3 flex flex-wrap gap-3.5">
                <a href="{{ route('listing', ['edit' => 'new']) }}" class="tc-btn-dark">Shop new arrivals</a>
                <a href="{{ route('contact') }}" class="tc-btn-outline">Get in touch</a>
            </div>
        </div>
    </div>

    {{-- Fashion that feels like you --}}
    <div class="border-t border-line bg-cream px-8 py-14 text-center md:px-16">
        <h2 data-reveal class="tc-heading">Fashion That Feels Like You</h2>
        <span data-reveal class="tc-heading-rule"></span>
        <p data-reveal class="mx-auto mt-7 max-w-[720px] text-[15.5px] font-light leading-[1.85] text-muted-3">
            From everyday essentials to standout pieces, our collection is chosen with one goal in mind:
            to help you find clothes that make you feel confident and beautiful. As a family-owned boutique
            we value the personal connection we build with every customer — Trendy Closet should feel like
            more than a store, somewhere you discover your next favourite outfit and feel right at home.
        </p>
    </div>

    {{-- Why Trendy Closet --}}
    <div class="px-8 py-14 md:px-16">
        <h2 data-reveal class="tc-heading">Why Trendy Closet?</h2>
        <span data-reveal class="tc-heading-rule"></span>
        <div data-reveal-children class="mx-auto mt-9 grid max-w-[1100px] grid-cols-1 gap-px bg-line sm:grid-cols-2 lg:grid-cols-3">
            @foreach($reasons as $r)
                <div class="bg-white p-9 text-center">
                    <div class="text-[24px]">{{ $r['icon'] }}</div>
                    <div class="mt-3 text-[16px] font-medium">{{ $r['title'] }}</div>
                    <div class="mt-1.5 text-[13.5px] font-light leading-[1.6] text-muted-2">{{ $r['body'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Visit us --}}
    <div class="flex flex-col border-t border-line lg:flex-row">
        <div class="flex flex-1 flex-col justify-center gap-4 px-8 py-14 md:px-16">
            <div class="text-[12px] font-medium tracking-[0.28em] text-blush">VISIT US</div>
            <div class="text-[30px] font-normal">Our Store</div>
            <p class="text-[15px] font-light leading-[1.8] text-muted-3">Come try pieces on and say hi — Pamela is usually in, and there's always something new on the rail.</p>
            <div class="mt-1.5 text-[14.5px] font-light leading-[1.9] text-ink">
                Trendy Closet<br>{!! implode('<br>', array_map('e', $contact['address'])) !!}<br><br>
                {!! implode('<br>', array_map('e', $contact['hours'])) !!}
            </div>
            <a href="{{ $contact['map_url'] }}" target="_blank" rel="noopener"
               class="tc-link mt-1.5 w-fit text-[13.5px]">Get directions</a>
        </div>
        <div class="relative min-h-[360px] flex-1">
            <iframe src="{{ $contact['map_embed'] }}" class="absolute inset-0 h-full w-full [filter:saturate(.75)_contrast(1.02)]" style="border:0" loading="lazy" title="Trendy Closet on the map"></iframe>
        </div>
    </div>

    {{-- Instagram band --}}
    <div class="bg-ink px-8 py-11 text-center text-white md:px-16">
        <div class="text-[26px] font-normal">Stay trendy. Stay confident. Stay you.</div>
        <div class="mt-2 text-[14px] font-light text-cream">@trendycloset.byleilakonsol</div>
        <a href="{{ route('listing') }}" class="tc-btn-outline mt-6 border-cream py-3.5 text-[14px] text-cream hover:bg-cream hover:text-ink">Shop the closet</a>
    </div>
@endsection
