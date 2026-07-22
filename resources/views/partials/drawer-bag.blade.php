{{-- Bag drawer contents. Served on its own by CartController::drawer and
     swapped into partials/drawer whenever the bag changes, so the panel never
     shows a stale bag. --}}
<div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <div class="text-[15px] font-medium tracking-[0.06em]">YOUR BAG ({{ $count }})</div>
        <button type="button" data-drawer-close aria-label="Close bag" class="p-1 text-[22px] leading-none transition-colors hover:text-blush">×</button>
    </div>

    @if($lines->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 text-center">
            <div class="text-[17px] font-normal">Your bag is empty</div>
            <div class="text-[14px] font-light text-muted-2">Nothing saved here yet — the good stuff is one tap away.</div>
            <a href="{{ route('listing') }}" class="tc-btn-dark mt-2">Start shopping</a>
        </div>
    @else
        <div class="flex-1 overflow-y-auto px-5 py-4">
            @foreach($lines as $line)
                <div class="flex gap-3.5 border-b border-line py-4 last:border-0">
                    <a href="{{ route('product', $line['variant']->product) }}" class="h-[92px] w-[72px] shrink-0 overflow-hidden rounded-lg bg-cream">
                        @if($line['variant']->product->image_url)
                            <img src="{{ $line['variant']->product->image_url }}" alt="{{ $line['variant']->product->name }}" class="h-full w-full object-cover">
                        @endif
                    </a>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('product', $line['variant']->product) }}" class="block truncate text-[14.5px] font-normal transition-colors hover:text-blush">{{ $line['variant']->product->name }}</a>
                        <div class="mt-0.5 text-[13px] font-light text-muted-2">{{ $line['variant']->label }}</div>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <form method="POST" action="{{ route('cart.update', $line['variant']) }}" data-async data-drawer-refresh class="flex items-center border border-line-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" name="quantity" value="{{ $line['qty'] - 1 }}" aria-label="Decrease quantity" class="px-2.5 py-1 text-[15px] leading-none transition-colors hover:text-blush">−</button>
                                <span class="min-w-[26px] border-x border-line-2 px-1 py-1 text-center text-[13.5px] font-medium">{{ $line['qty'] }}</span>
                                <button type="submit" name="quantity" value="{{ $line['qty'] + 1 }}" aria-label="Increase quantity" class="px-2.5 py-1 text-[15px] leading-none transition-colors hover:text-blush">+</button>
                            </form>
                            <span class="text-[14.5px] font-semibold text-blush">{{ \App\Models\Product::money($line['total']) }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('cart.remove', $line['variant']) }}" data-async data-drawer-refresh>
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Remove {{ $line['variant']->product->name }}" class="p-1 text-[13px] font-light text-muted-2 underline underline-offset-2 transition-colors hover:text-blush">Remove</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="border-t border-line px-5 py-4">
            @if($freeShippingRemainder > 0)
                <div class="mb-3 rounded-lg bg-cream-3 px-3 py-2 text-center text-[13px] font-light">
                    {{ \App\Models\Product::money($freeShippingRemainder) }} away from free shipping
                </div>
            @endif
            <div class="flex items-center justify-between text-[15px]">
                <span class="font-light text-muted-2">Subtotal</span>
                <span class="text-[18px] font-semibold text-blush">{{ \App\Models\Product::money($summary['subtotal']) }}</span>
            </div>
            <div class="mt-1 text-[12.5px] font-light text-muted-2">Shipping and discounts are calculated at checkout.</div>
            <div class="mt-4 flex flex-col gap-2.5">
                <a href="{{ route('checkout') }}" class="tc-btn-dark w-full text-center">Checkout</a>
                <a href="{{ route('cart') }}" class="tc-btn-outline w-full text-center">View bag</a>
            </div>
        </div>
    @endif
</div>
