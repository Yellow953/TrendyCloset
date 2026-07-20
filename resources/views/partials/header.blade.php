@php($active = $active ?? null)
<header class="relative z-40">
    {{-- Announcement bar --}}
    <div class="flex items-center justify-between bg-ink px-5 py-2.5 text-[12px] font-light text-cream md:px-10 md:text-[13px]">
        <div>Free worldwide shipping on orders over $150 —
            <a href="{{ route('listing') }}" class="font-medium underline underline-offset-2">Shop now</a>
        </div>
        <div class="hidden gap-6 sm:flex">
            <a href="{{ route('about') }}">About</a>
            <span>Blog</span>
            <a href="{{ route('contact') }}">Contact</a>
            <span>FAQs</span>
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
            <span class="flex h-[42px] w-[42px] items-center justify-center rounded-full border-2 border-ink bg-tan text-center text-[8px] font-normal leading-[1.15] tracking-[0.06em] text-ink outline-2 outline-tan md:h-[52px] md:w-[52px] md:text-[9px]">TRENDY<br>CLOSET</span>
            <span>
                <span class="block text-[16px] font-semibold tracking-[0.14em] text-ink sm:text-[20px] md:text-[24px] md:tracking-[0.16em]">TRENDY CLOSET</span>
                <span class="hidden font-serif text-[12px] italic tracking-[0.18em] text-muted sm:block">by Leila Konsol</span>
            </span>
        </a>

        <nav class="hidden items-center gap-[30px] text-[13.5px] font-medium tracking-[0.08em] lg:flex">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-blush' : 'hover:text-blush' }}">HOME</a>
            <span class="group/mega static">
                <a href="{{ route('listing') }}" class="inline-flex items-center gap-1.5 py-1 {{ in_array($active, ['women']) ? 'border-b-2 border-blush text-blush' : 'hover:text-blush' }}">WOMEN <span class="text-[10px]">▾</span></a>
                {{-- Mega menu --}}
                <div class="invisible absolute inset-x-0 top-full z-30 flex gap-14 border-b border-line-3 bg-white px-16 pb-10 pt-8 opacity-0 shadow-[0_34px_54px_rgba(43,37,35,.14)] transition-all duration-150 group-hover/mega:visible group-hover/mega:opacity-100">
                    <div class="flex-1">
                        <div class="mb-3.5 text-[12px] font-medium tracking-[0.18em] text-blush">CLOTHING</div>
                        <div class="text-[14px] font-light leading-[2.2] text-muted-3">Dresses<br>Tops &amp; Shirts<br>Trousers &amp; Skirts<br>Knitwear<br>Outerwear</div>
                    </div>
                    <div class="flex-1">
                        <div class="mb-3.5 text-[12px] font-medium tracking-[0.18em] text-blush">SHOES &amp; BAGS</div>
                        <div class="text-[14px] font-light leading-[2.2] text-muted-3">Sneakers<br>Sandals<br>Totes<br>Crossbody<br>Mini bags</div>
                    </div>
                    <div class="flex-1">
                        <div class="mb-3.5 text-[12px] font-medium tracking-[0.18em] text-blush">EDITS</div>
                        <div class="text-[14px] font-light leading-[2.2] text-muted-3">New in<br>Leila's picks<br>Summer dresses<br>Workwear<br>Sale up to 40%</div>
                    </div>
                    <div class="flex-[0_0_300px]">
                        <div class="h-[180px] overflow-hidden rounded"><img src="https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?q=60&w=700&auto=format&fit=crop" alt="The Summer Edit" class="h-full w-full object-cover"></div>
                        <div class="mt-3 text-[15px] font-normal">The Summer Edit is live</div>
                        <a href="{{ route('listing') }}" class="mt-1 inline-block text-[13px] font-medium text-blush underline underline-offset-2">Shop the edit</a>
                    </div>
                </div>
            </span>
            <a href="{{ route('listing') }}" class="{{ $active === 'men' ? 'text-blush' : 'hover:text-blush' }}">MEN</a>
            <a href="{{ route('listing') }}" class="{{ $active === 'kids' ? 'text-blush' : 'hover:text-blush' }}">KIDS</a>
            <a href="{{ route('listing') }}" class="flex items-center gap-1.5 hover:text-blush">SALE <span class="rounded-[2px] bg-blush px-1.5 py-0.5 text-[10px] text-white">-40%</span></a>
            <a href="{{ route('listing') }}" class="flex items-center gap-1.5 hover:text-blush">NEW <span class="rounded-[2px] bg-ink px-1.5 py-0.5 text-[10px] text-white">HOT</span></a>
        </nav>

        <div class="flex items-center gap-3 text-[13px] font-light md:gap-[18px]">
            <span class="hidden sm:inline">Search</span>
            <a href="{{ route('login') }}" class="hidden sm:inline">Account</a>
            <span class="hidden sm:inline">♡ 2</span>
            <a href="{{ route('cart') }}" class="whitespace-nowrap font-medium {{ $bagCount > 0 ? 'text-blush' : '' }}">Bag ({{ $bagCount }})<span class="hidden sm:inline"> · {{ $bagTotal }}</span></a>
        </div>
    </div>

    {{-- Mobile drawer (toggled by the checkbox above) --}}
    <div class="hidden border-b border-line bg-white peer-checked/mnav:block lg:!hidden">
        <nav class="flex flex-col px-5 py-2 text-[15px] font-medium tracking-[0.04em]">
            <a href="{{ route('home') }}" class="border-b border-line py-3.5 {{ $active === 'home' ? 'text-blush' : '' }}">HOME</a>
            <a href="{{ route('listing') }}" class="flex items-center justify-between border-b border-line py-3.5 {{ $active === 'women' ? 'text-blush' : '' }}">WOMEN</a>
            <a href="{{ route('listing') }}" class="border-b border-line py-3.5 {{ $active === 'men' ? 'text-blush' : '' }}">MEN</a>
            <a href="{{ route('listing') }}" class="border-b border-line py-3.5 {{ $active === 'kids' ? 'text-blush' : '' }}">KIDS</a>
            <a href="{{ route('listing') }}" class="flex items-center gap-2 border-b border-line py-3.5">SALE <span class="rounded-[2px] bg-blush px-1.5 py-0.5 text-[10px] text-white">-40%</span></a>
            <a href="{{ route('listing') }}" class="flex items-center gap-2 border-b border-line py-3.5">NEW <span class="rounded-[2px] bg-ink px-1.5 py-0.5 text-[10px] text-white">HOT</span></a>
            <div class="flex gap-6 py-4 text-[13px] font-light text-muted-2">
                <span>Search</span>
                <a href="{{ route('login') }}">Account</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </nav>
    </div>
</header>
