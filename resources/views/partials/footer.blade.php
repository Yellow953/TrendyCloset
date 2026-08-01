{{-- $navTree / $catalog come from the view composer in AppServiceProvider. --}}
@php
    $contact = config('store.contact');
    $whatsapp = config('store.whatsapp');
@endphp
<footer class="border-t border-line bg-white">
    {{-- Newsletter, folded into the footer as its opening band --}}
    <div class="border-b border-line px-5 py-12 md:px-10">
        <div class="mx-auto flex max-w-[1200px] flex-col items-center gap-6 text-center lg:flex-row lg:justify-between lg:text-left">
            <div>
                <div class="text-[24px] font-normal">Join the Closet</div>
                <div class="mt-1.5 text-[14px] font-light text-muted-2">10% off your first order, plus new drops from Leila's feed before anyone else.</div>
            </div>
            {{-- One rounded field with the button living inside its right end,
                 rather than two squares butted together. --}}
            <form class="flex w-full max-w-md overflow-hidden rounded-field border border-line-2 bg-white transition-colors focus-within:border-blush lg:w-auto">
                <input type="email" placeholder="Your email address" aria-label="Your email address"
                       class="w-full bg-transparent px-5 py-3.5 text-[14px] font-light text-ink placeholder:text-muted outline-none lg:w-[300px]">
                <button type="submit" class="whitespace-nowrap bg-ink px-7 py-3.5 text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Subscribe</button>
            </form>
        </div>
    </div>

    {{-- Columns --}}
    <div class="mx-auto grid max-w-[1280px] grid-cols-1 gap-10 px-5 py-14 md:px-10 sm:grid-cols-2 lg:grid-cols-[2fr_.9fr_.9fr_1fr]">
        <div>
            <a href="{{ route('home') }}" class="mb-6 flex items-center gap-3">
                <img src="{{ asset('images/logo-192.png') }}" alt="Trendy Closet"
                     srcset="{{ asset('images/logo-64.png') }} 64w, {{ asset('images/logo-192.png') }} 192w"
                     sizes="48px" width="48" height="48" loading="lazy" decoding="async"
                     class="h-12 w-12 shrink-0 object-contain">
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
                <a href="{{ $contact['map_url'] }}" target="_blank" rel="noopener" class="flex gap-3 transition-colors hover:text-blush">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-blush"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    <span>{!! implode('<br>', array_map('e', $contact['address'])) !!}</span>
                </a>
                @if(! empty($whatsapp['number']))
                    <a href="https://wa.me/{{ $whatsapp['number'] }}" target="_blank" rel="noopener" class="flex gap-3 transition-colors hover:text-blush">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-blush"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.39a9.86 9.86 0 0 0 4.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.02h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.03-.2-.31a8.19 8.19 0 0 1-1.26-4.37c0-4.54 3.7-8.23 8.23-8.23a8.17 8.17 0 0 1 5.82 2.42 8.18 8.18 0 0 1 2.4 5.82c0 4.54-3.69 8.21-8.22 8.21Zm4.51-6.16c-.25-.12-1.46-.72-1.69-.8-.23-.09-.39-.13-.56.12-.16.25-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.13-1.04-.39-1.99-1.23-.73-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.42l-.47-.01c-.16 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.54c.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.18 1.12.16 1.54.1.47-.07 1.46-.6 1.66-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.29Z"/></svg>
                        <span>{{ $contact['phone_display'] }}</span>
                    </a>
                @endif
                <a href="mailto:{{ config('seo.email') }}" class="flex gap-3 transition-colors hover:text-blush">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-blush"><rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m3.5 6.5 8.5 6 8.5-6"/></svg>
                    <span class="break-all">{{ config('seo.email') }}</span>
                </a>
                <div class="text-[13.5px]">{{ $contact['hours'] }}</div>
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
