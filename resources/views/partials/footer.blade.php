<footer>
    {{-- Newsletter --}}
    <div class="flex flex-col items-start justify-between gap-6 bg-tan px-5 md:px-10 py-11 text-ink md:flex-row md:items-center">
        <div>
            <div class="text-[24px] font-normal">Join the Closet</div>
            <div class="mt-1.5 text-[14px] font-light text-muted-2">10% off your first order + new drops from Leila's feed.</div>
        </div>
        <form class="flex w-full max-w-md md:w-auto">
            <input type="email" placeholder="Your email address" class="w-full bg-white px-5 py-3.5 text-[14px] font-light text-ink placeholder:text-muted outline-none md:w-[280px]">
            <button type="submit" class="bg-ink px-7 py-3.5 text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Subscribe</button>
        </form>
    </div>

    {{-- Columns --}}
    <div class="grid grid-cols-2 gap-10 bg-ink px-5 md:px-10 py-11 text-cream md:grid-cols-4 lg:flex lg:gap-12">
        <div class="col-span-2 lg:flex-[1.4]">
            <div class="text-[18px] font-semibold tracking-[0.14em] text-white">TRENDY CLOSET</div>
            <div class="mt-3 max-w-[300px] text-[13.5px] font-light leading-[1.7]">A curated closet for the whole family — picked with love by Leila Konsol.</div>
        </div>
        <div class="lg:flex-1">
            <div class="mb-3 text-[14px] font-medium text-white">Shop</div>
            <div class="text-[13.5px] font-light leading-[2]"><a href="{{ route('listing') }}" class="hover:text-white">Women</a><br><a href="{{ route('listing') }}" class="hover:text-white">Men</a><br><a href="{{ route('listing') }}" class="hover:text-white">Kids</a><br><a href="{{ route('listing') }}" class="hover:text-white">Sale</a><br><a href="{{ route('listing') }}" class="hover:text-white">New Arrivals</a></div>
        </div>
        <div class="lg:flex-1">
            <div class="mb-3 text-[14px] font-medium text-white">Help</div>
            <div class="text-[13.5px] font-light leading-[2]"><a href="{{ route('policies') }}" class="hover:text-white">Shipping &amp; Delivery</a><br><a href="{{ route('policies') }}" class="hover:text-white">Returns &amp; Refunds</a><br><a href="{{ route('policies') }}" class="hover:text-white">Size Guide</a><br>Track Order<br>FAQs</div>
        </div>
        <div class="lg:flex-1">
            <div class="mb-3 text-[14px] font-medium text-white">Contact</div>
            <div class="text-[13.5px] font-light leading-[2]">@trendycloset.byleilakonsol<br>hello@trendycloset.com<br>Mon–Sat, 9am–6pm</div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="flex flex-col items-center justify-between gap-2 bg-ink-deep px-5 md:px-10 py-4 text-[13px] font-light text-muted sm:flex-row">
        <div>© {{ date('Y') }} Trendy Closet by Leila Konsol</div>
        <div>Visa · Mastercard · PayPal · Apple Pay</div>
    </div>
</footer>
