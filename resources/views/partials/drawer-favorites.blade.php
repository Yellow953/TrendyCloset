{{-- Favourites drawer contents. Served by StoreController::favoritesDrawer and
     swapped into partials/drawer. Un-hearting here re-fetches the fragment, so
     the list and the header badge never disagree. --}}
<div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <div class="text-[15px] font-medium tracking-[0.06em]">FAVOURITES ({{ $products->count() }})</div>
        <button type="button" data-drawer-close aria-label="Close favourites" class="p-1 text-[22px] leading-none transition-colors hover:text-blush">×</button>
    </div>

    @if($products->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 text-center">
            <div class="text-[17px] font-normal">Nothing saved yet</div>
            <div class="text-[14px] font-light text-muted-2">Tap ♡ on a piece you like and it will wait for you here.</div>
            <a href="{{ route('listing') }}" class="tc-btn-dark mt-2">Browse the shop</a>
        </div>
    @else
        <div class="flex-1 overflow-y-auto px-5 py-4">
            @foreach($products as $p)
                <div class="flex gap-3.5 border-b border-line py-4 last:border-0">
                    <a href="{{ route('product', $p) }}" class="h-[92px] w-[72px] shrink-0 overflow-hidden rounded-lg bg-cream">
                        @if($p->image_url)
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="h-full w-full object-cover">
                        @endif
                    </a>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('product', $p) }}" class="block truncate text-[14.5px] font-normal transition-colors hover:text-blush">{{ $p->name }}</a>
                        <div class="mt-1 text-[14.5px] font-semibold text-blush">{{ $p->price_label }}</div>
                        <div class="mt-2 flex items-center gap-3">
                            @if($p->default_variant)
                                <form method="POST" action="{{ route('cart.add') }}" data-async data-drawer-refresh>
                                    @csrf
                                    <input type="hidden" name="variant_id" value="{{ $p->default_variant->id }}">
                                    <button type="submit" class="border border-ink px-3 py-1.5 text-[12.5px] font-medium transition-colors hover:bg-ink hover:text-white">Add to bag</button>
                                </form>
                            @else
                                <span class="text-[12.5px] font-light text-muted-2">Sold out</span>
                            @endif

                            <form method="POST" action="{{ route('product.favorite', $p) }}" data-async data-drawer-refresh>
                                @csrf
                                <button type="submit" class="p-1 text-[13px] font-light text-muted-2 underline underline-offset-2 transition-colors hover:text-blush">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-line px-5 py-4">
            <a href="{{ route('favorites') }}" class="tc-btn-dark w-full text-center">View all favourites</a>
        </div>
    @endif
</div>
