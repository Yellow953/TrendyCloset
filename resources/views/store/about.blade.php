@extends('layouts.storefront')

@section('title', 'Our Story — Trendy Closet')

@section('content')
    {{-- Hero --}}
    <div class="relative h-[340px] overflow-hidden bg-tan">
        <img src="{{ $hero['img'] }}" alt="Trendy Closet studio" class="absolute inset-0 h-full w-full object-cover">
        {{-- Scrim keeps the copy readable whatever the photo does --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-cream-2/85 via-cream-2/45 to-transparent"></div>
        <div class="pointer-events-none absolute inset-0 flex flex-col justify-center px-8 md:px-16">
            <div class="text-[12px] font-medium tracking-[0.28em] text-blush-soft">OUR STORY</div>
            <div class="mt-2.5 text-[34px] font-light leading-[1.15] text-ink md:text-[46px]">Curated by Leila,<br><span class="font-serif font-medium italic text-blush">for the whole family</span></div>
        </div>
    </div>

    {{-- Story --}}
    <div class="flex flex-col items-center gap-14 px-8 py-14 md:px-16 lg:flex-row">
        <div class="h-[380px] w-full flex-1 overflow-hidden"><img src="{{ $portrait['img'] }}" alt="Leila Konsol" class="h-full w-full object-cover"></div>
        <div class="flex flex-1 flex-col gap-4">
            <div class="text-[30px] font-normal">Hi, I'm Leila</div>
            <p class="text-[15px] font-light leading-[1.75] text-muted-3">Trendy Closet started as a small Instagram page sharing outfits I loved. Today it's a curated shop for women, men and kids — every piece hand-picked, every drop styled by me before it ever ships.</p>
            <p class="text-[15px] font-light leading-[1.75] text-muted-3">No trend-chasing, no filler — just pieces I'd wear myself and want in your closet too.</p>
            <div class="mt-2.5 flex gap-10">
                <div><div class="text-[24px] font-semibold text-blush">2021</div><div class="text-[12.5px] font-light text-muted">founded</div></div>
                <div><div class="text-[24px] font-semibold text-blush">24k</div><div class="text-[12.5px] font-light text-muted">followers</div></div>
                <div><div class="text-[24px] font-semibold text-blush">{{ $catalogSize }}</div><div class="text-[12.5px] font-light text-muted">curated pieces</div></div>
                <div><div class="text-[24px] font-semibold text-blush">{{ number_format($storeRating, 1) }}★</div><div class="text-[12.5px] font-light text-muted">store rating</div></div>
            </div>
            <div class="mt-3 flex flex-wrap gap-3.5">
                <a href="{{ route('listing', ['edit' => 'featured']) }}" class="tc-btn-dark">Shop Leila's picks</a>
                <a href="{{ route('contact') }}" class="tc-btn-outline">Get in touch</a>
            </div>
        </div>
    </div>

    {{-- Values --}}
    <div class="grid grid-cols-1 gap-px bg-line sm:grid-cols-3">
        <div class="bg-cream p-10 text-center"><div class="text-[26px] text-blush">✦</div><div class="mt-3 text-[16px] font-medium">Hand-picked</div><div class="mt-1.5 text-[13.5px] font-light leading-[1.6] text-muted-2">Every item chosen and worn by Leila first.</div></div>
        <div class="bg-cream p-10 text-center"><div class="text-[26px] text-blush">✦</div><div class="mt-3 text-[16px] font-medium">Family first</div><div class="mt-1.5 text-[13.5px] font-light leading-[1.6] text-muted-2">Women's, men's and kids' pieces in one closet.</div></div>
        <div class="bg-cream p-10 text-center"><div class="text-[26px] text-blush">✦</div><div class="mt-3 text-[16px] font-medium">Community</div><div class="mt-1.5 text-[13.5px] font-light leading-[1.6] text-muted-2">Styled and shared daily with 24k followers.</div></div>
    </div>

    {{-- Visit us --}}
    <div class="flex flex-col border-t border-line lg:flex-row">
        <div class="flex flex-1 flex-col justify-center gap-4 px-8 py-14 md:px-16">
            <div class="text-[12px] font-medium tracking-[0.28em] text-blush">VISIT US</div>
            <div class="text-[30px] font-normal">Our Flagship Store</div>
            <p class="text-[15px] font-light leading-[1.8] text-muted-3">Come try pieces on and say hi — Leila's often in on weekends.</p>
            <div class="mt-1.5 text-[14.5px] font-light leading-[1.9] text-ink">
                123 Rue de la Mode<br>75003 Paris, France<br><br>
                Tue–Sat, 11am–7pm<br>Sun–Mon, closed
            </div>
            <a href="https://maps.google.com/?q=123+Rue+de+la+Mode,+75003+Paris,+France" target="_blank" rel="noopener"
               class="tc-link mt-1.5 w-fit text-[13.5px]">Get directions</a>
        </div>
        <div class="relative min-h-[360px] flex-1">
            <iframe src="https://maps.google.com/maps?q=123%20Rue%20de%20la%20Mode%2C%2075003%20Paris%2C%20France&t=&z=15&ie=UTF8&iwloc=&output=embed" class="absolute inset-0 h-full w-full [filter:saturate(.75)_contrast(1.02)]" style="border:0" loading="lazy" title="Trendy Closet flagship store"></iframe>
        </div>
    </div>

    {{-- Instagram band --}}
    <div class="bg-ink px-8 py-11 text-center text-white md:px-16">
        <div class="text-[26px] font-normal">Follow the journey on Instagram</div>
        <div class="mt-2 text-[14px] font-light text-cream">@trendycloset.byleilakonsol</div>
        <a href="{{ route('listing') }}" class="mt-6 inline-flex items-center justify-center border border-cream px-8 py-3.5 text-[14px] font-medium tracking-[0.06em] text-cream transition-colors hover:bg-cream hover:text-ink">Shop the closet</a>
    </div>
@endsection
