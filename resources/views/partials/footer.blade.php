{{-- $navTree / $catalog come from the view composer in AppServiceProvider. --}}
<footer class="border-t border-line bg-white">
    {{-- Newsletter, folded into the footer as its opening band --}}
    <div class="border-b border-line px-5 py-12 md:px-10">
        <div class="mx-auto flex max-w-[1200px] flex-col items-center gap-6 text-center lg:flex-row lg:justify-between lg:text-left">
            <div>
                <div class="text-[24px] font-normal">Join the Closet</div>
                <div class="mt-1.5 text-[14px] font-light text-muted-2">10% off your first order, plus new drops from Leila's feed before anyone else.</div>
            </div>
            <form class="flex w-full max-w-md lg:w-auto">
                <input type="email" placeholder="Your email address" aria-label="Your email address"
                       class="w-full border border-line-2 bg-white px-5 py-3.5 text-[14px] font-light text-ink placeholder:text-muted outline-none transition-colors focus:border-blush lg:w-[300px]">
                <button type="submit" class="whitespace-nowrap bg-ink px-7 py-3.5 text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Subscribe</button>
            </form>
        </div>
    </div>

    {{-- Columns --}}
    <div class="mx-auto grid max-w-[1280px] grid-cols-1 gap-10 px-5 py-14 md:px-10 sm:grid-cols-2 lg:grid-cols-[2fr_.9fr_.9fr_1fr]">
        <div>
            <a href="{{ route('home') }}" class="mb-6 flex items-center gap-3">
                <img src="{{ asset('images/logo-192.png') }}" alt="Trendy Closet" class="h-12 w-12 shrink-0 object-contain">
                <span class="tc-wordmark text-[19px] text-ink">Trendy Closet</span>
            </a>
            <div class="text-[17px] font-medium">About Our Store</div>
            <p class="mt-4 max-w-[330px] text-[14.5px] font-light leading-[1.8] text-muted-2">
                Trendy Closet started as a small Instagram page sharing outfits Leila loved. Every piece is
                hand-picked and styled by her before it ever ships — no trend-chasing, no filler.
            </p>
            <a href="{{ route('about') }}" class="tc-link mt-4 inline-block text-[13.5px]">Read our story</a>
        </div>

        <div>
            <div class="text-[17px] font-medium">Shop</div>
            <div class="mt-4 flex flex-col gap-2.5 text-[14.5px] font-light text-muted-2">
                @foreach($navTree->take(4) as $root)
                    <a href="{{ route('listing', $root) }}" class="transition-colors hover:text-blush">{{ $root->name }}</a>
                @endforeach
                <a href="{{ route('listing', ['edit' => 'new']) }}" class="transition-colors hover:text-blush">New Arrivals</a>
                <a href="{{ route('listing', ['edit' => 'sale']) }}" class="transition-colors hover:text-blush">Sale</a>
            </div>
        </div>

        <div>
            <div class="text-[17px] font-medium">Policies</div>
            <div class="mt-4 flex flex-col gap-2.5 text-[14.5px] font-light text-muted-2">
                <a href="{{ route('policies', 'shipping') }}" class="transition-colors hover:text-blush">Shipping &amp; Delivery</a>
                <a href="{{ route('policies', 'returns') }}" class="transition-colors hover:text-blush">Returns &amp; Refunds</a>
                <a href="{{ route('policies', 'size-guide') }}" class="transition-colors hover:text-blush">Size Guide</a>
                <a href="{{ route('policies', 'privacy') }}" class="transition-colors hover:text-blush">Privacy Policy</a>
                <a href="{{ route('policies', 'terms') }}" class="transition-colors hover:text-blush">Terms of Service</a>
            </div>
        </div>

        <div>
            <div class="text-[17px] font-medium">Contact Us</div>
            <div class="mt-4 flex flex-col gap-3.5 text-[14.5px] font-light leading-[1.6] text-muted-2">
                <div class="flex gap-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-blush"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    <span>123 Rue de la Mode<br>75003 Paris, France</span>
                </div>
                <a href="tel:+33100000000" class="flex gap-3 transition-colors hover:text-blush">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-blush"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1Z"/></svg>
                    <span>(+33) 1 00 00 00 00</span>
                </a>
                <a href="mailto:hello@trendycloset.com" class="flex gap-3 transition-colors hover:text-blush">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-blush"><rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m3.5 6.5 8.5 6 8.5-6"/></svg>
                    <span>hello@trendycloset.com</span>
                </a>
                <div class="text-[13.5px]">Mon–Sat, 9am–6pm</div>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-line px-5 py-6 md:px-10">
            <div class="w-[100%] text-center text-[13px] font-light text-muted md:order-none">
                © {{ date('Y') }} Trendy Closet by Leila Konsol
            </div>
    </div>
</footer>
