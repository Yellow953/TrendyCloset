{{-- $navTree, $catalog, $bagCount, $bagTotal and $favoritesCount are supplied
     by the view composer in AppServiceProvider. --}}
@php
    $active = $active ?? null;
    $spotlight = $catalog->spotlight();
    // Roots with subcategories become mega-menu columns; the rest are listed
    // together so a flat category is never unreachable from the nav.
    $columns = $navTree->filter(fn ($c) => $c->children->isNotEmpty())->take(3);
    $flatRoots = $navTree->filter(fn ($c) => $c->children->isEmpty());
@endphp
{{-- Sticky: the nav follows the page, and the announcement bar above it
     collapses on scroll (see .is-scrolled in app.css, driven by app.js). --}}
<header data-header class="sticky top-0 z-40 bg-white transition-shadow">
    {{-- Announcement bar --}}
    <div data-announcement class="bg-ink text-center text-[12px] font-light text-cream md:text-[13px]">
        <div class="px-5 py-2.5 md:px-10">
            Free worldwide shipping on orders over {{ \App\Models\Product::money(\App\Support\Cart::FREE_SHIPPING_THRESHOLD) }} —
            <a href="{{ route('listing') }}" class="font-medium underline underline-offset-2">Shop now</a>
        </div>
    </div>

    {{-- CSS-only mobile menu toggle (no JS needed) --}}
    <input type="checkbox" id="tc-mobile-nav" class="peer/mnav hidden">

    {{-- Main nav --}}
    <div class="group/nav relative flex items-center justify-between border-b border-line px-5 py-4 md:px-10">
        {{-- Hamburger (mobile only) --}}
        <label for="tc-mobile-nav" class="-ml-1 mr-1 flex cursor-pointer flex-col gap-[5px] p-1 lg:hidden" aria-label="Toggle menu">
            <span class="block h-[2px] w-6 bg-ink transition-transform peer-checked/mnav:translate-y-[7px] peer-checked/mnav:rotate-45"></span>
            <span class="block h-[2px] w-6 bg-ink transition-opacity peer-checked/mnav:opacity-0"></span>
            <span class="block h-[2px] w-6 bg-ink transition-transform peer-checked/mnav:-translate-y-[7px] peer-checked/mnav:-rotate-45"></span>
        </label>

        <a href="{{ route('home') }}" class="flex items-center gap-2.5 md:gap-3.5">
            <img src="{{ asset('images/logo-192.png') }}" alt="Trendy Closet"
                 class="h-[42px] w-[42px] shrink-0 object-contain md:h-[52px] md:w-[52px]">
            <span>
                <span class="tc-wordmark block text-[16px] text-ink sm:text-[20px] md:text-[23px]">Trendy Closet</span>
                <span class="hidden font-serif text-[12px] italic tracking-[0.18em] text-muted sm:block">by Leila Konsol</span>
            </span>
        </a>

        <nav class="hidden items-center gap-[30px] text-[13.5px] font-medium tracking-[0.08em] lg:flex">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-blush' : 'hover:text-blush' }}">HOME</a>
            <span class="group/mega static">
                <a href="{{ route('listing') }}" class="inline-flex items-center gap-1.5 py-1 {{ $active === 'shop' ? 'border-b-2 border-blush text-blush' : 'hover:text-blush' }}">SHOP <span class="text-[10px]">▾</span></a>
                {{-- Mega menu, built from the category tree --}}
                <div class="invisible absolute inset-x-0 top-full z-30 flex gap-14 border-b border-line-3 bg-white px-16 pb-10 pt-8 opacity-0 shadow-[0_34px_54px_rgba(43,37,35,.14)] transition-all duration-150 group-hover/mega:visible group-hover/mega:opacity-100">
                    @foreach($columns as $column)
                        <div class="flex-1">
                            <a href="{{ route('listing', $column) }}" class="mb-3.5 block text-[12px] font-medium tracking-[0.18em] text-blush">{{ Str::upper($column->name) }}</a>
                            <div class="flex flex-col text-[14px] font-light leading-[2.2] text-muted-3">
                                @foreach($column->children as $child)
                                    <a href="{{ route('listing', $child) }}" class="hover:text-blush">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="flex-1">
                        <div class="mb-3.5 text-[12px] font-medium tracking-[0.18em] text-blush">EDITS</div>
                        <div class="flex flex-col text-[14px] font-light leading-[2.2] text-muted-3">
                            <a href="{{ route('listing', ['edit' => 'new']) }}" class="hover:text-blush">New in</a>
                            <a href="{{ route('listing', ['edit' => 'featured']) }}" class="hover:text-blush">Leila's picks</a>
                            <a href="{{ route('listing', ['edit' => 'sale']) }}" class="hover:text-blush">Sale</a>
                            @foreach($flatRoots as $root)
                                <a href="{{ route('listing', $root) }}" class="hover:text-blush">{{ $root->name }}</a>
                            @endforeach
                        </div>
                    </div>

                    @if($spotlight)
                        <div class="flex-[0_0_300px]">
                            <div class="h-[180px] overflow-hidden rounded"><img src="{{ $spotlight->image_url }}" alt="{{ $spotlight->name }}" class="h-full w-full object-cover"></div>
                            <div class="mt-3 text-[15px] font-normal">{{ $spotlight->name }}</div>
                            <a href="{{ route('product', $spotlight) }}" class="mt-1 inline-block text-[13px] font-medium text-blush underline underline-offset-2">Shop the piece</a>
                        </div>
                    @endif
                </div>
            </span>
            <a href="{{ route('about') }}" class="{{ $active === 'about' ? 'text-blush' : 'hover:text-blush' }}">ABOUT</a>
            <a href="{{ route('contact') }}" class="{{ $active === 'contact' ? 'text-blush' : 'hover:text-blush' }}">CONTACT</a>
        </nav>

        {{-- Icon actions. The counters are badges rather than inline text. --}}
        <div class="flex items-center gap-4 md:gap-5">
            <button type="button" aria-label="Search" class="transition-colors hover:text-blush">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-[22px] w-[22px]"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
            </button>

            <a href="{{ route('login') }}" aria-label="Account" class="transition-colors hover:text-blush">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-[22px] w-[22px]"><circle cx="12" cy="8.5" r="3.8"/><path d="M4.8 20a7.2 7.2 0 0 1 14.4 0"/></svg>
            </a>

            <a href="{{ route('favorites') }}" aria-label="Favourites ({{ $favoritesCount }})" class="relative transition-colors hover:text-blush">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-[22px] w-[22px]"><path d="M12 20.5 4.6 13.3a4.5 4.5 0 1 1 6.4-6.3l1 1 1-1a4.5 4.5 0 1 1 6.4 6.3Z"/></svg>
                <span class="absolute -right-2 -top-1.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-blush px-1 text-[10px] font-medium text-white">{{ $favoritesCount }}</span>
            </a>

            <a href="{{ route('cart') }}" aria-label="Bag ({{ $bagCount }})" class="flex items-center gap-2 transition-colors hover:text-blush">
                <span class="relative">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-[22px] w-[22px]"><path d="M6 7.5h12l-1 12.5H7L6 7.5Z"/><path d="M9 7.5a3 3 0 0 1 6 0"/></svg>
                    <span class="absolute -right-2 -top-1.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-blush px-1 text-[10px] font-medium text-white">{{ $bagCount }}</span>
                </span>
                <span class="hidden text-[14px] font-medium sm:inline">{{ $bagTotal }}</span>
            </a>
        </div>
    </div>

    {{-- Mobile drawer (toggled by the checkbox above) --}}
    <div class="hidden border-b border-line bg-white peer-checked/mnav:block lg:!hidden">
        <nav class="flex flex-col px-5 py-2 text-[15px] font-medium tracking-[0.04em]">
            <a href="{{ route('home') }}" class="border-b border-line py-3.5 {{ $active === 'home' ? 'text-blush' : '' }}">HOME</a>
            @foreach($navTree as $root)
                <a href="{{ route('listing', $root) }}" class="flex items-center justify-between border-b border-line py-3.5">
                    {{ Str::upper($root->name) }}
                    <span class="text-[12px] font-light text-muted">{{ $catalog->countFor($root) }}</span>
                </a>
            @endforeach
            <a href="{{ route('about') }}" class="border-b border-line py-3.5 {{ $active === 'about' ? 'text-blush' : '' }}">ABOUT</a>
            <a href="{{ route('contact') }}" class="border-b border-line py-3.5 {{ $active === 'contact' ? 'text-blush' : '' }}">CONTACT</a>
            <div class="flex gap-6 py-4 text-[13px] font-light text-muted-2">
                <span>Search</span>
                <a href="{{ route('login') }}">Account</a>
                <a href="{{ route('favorites') }}">Favourites ({{ $favoritesCount }})</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </nav>
    </div>
</header>
