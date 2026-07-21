@extends('layouts.storefront')

@section('content')
    <div class="px-5 md:px-10 pb-3 pt-9">
        <h1 class="text-[32px] font-normal">Your Bag <span class="text-[18px] font-light text-muted">({{ $lines->sum('qty') }} {{ Str::plural('item', $lines->sum('qty')) }})</span></h1>
    </div>

    @if($lines->isEmpty())
        <div class="px-5 md:px-10 pb-20 pt-6">
            <div class="border border-line bg-cream-3 px-6 py-20 text-center">
                <div class="text-[22px] font-normal">Your bag is empty</div>
                <div class="mt-2 text-[14.5px] font-light text-muted-2">Nothing saved for later yet — the new drop is a good place to start.</div>
                <a href="{{ route('listing', ['edit' => 'new']) }}" class="tc-btn-dark mt-6">Shop New In</a>
            </div>
        </div>
    @else
        <div class="flex flex-col gap-10 px-5 md:px-10 pb-12 pt-5 lg:flex-row">
            {{-- Items --}}
            <div class="lg:flex-[1.6]">
                <div class="hidden border-b border-line pb-3 text-[12px] font-medium tracking-[0.1em] text-muted sm:flex">
                    <div class="flex-[2.2]">PRODUCT</div><div class="flex-1 text-center">QTY</div><div class="flex-[0.8] text-right">TOTAL</div>
                </div>
                @foreach($lines as $line)
                    @php($variant = $line['variant'])
                    @php($product = $variant->product)
                    <div class="flex flex-wrap items-center gap-y-4 border-b border-line py-5">
                        <div class="flex w-full items-center gap-4 sm:w-auto sm:flex-[2.2]">
                            <a href="{{ route('product', $product) }}" class="h-[92px] w-[76px] flex-none overflow-hidden bg-cream">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @endif
                            </a>
                            <div>
                                <a href="{{ route('product', $product) }}" class="text-[15.5px] font-normal hover:text-blush">{{ $product->name }}</a>
                                <div class="mt-1 text-[13px] font-light text-muted">{{ $variant->label }}</div>
                                <form method="POST" action="{{ route('cart.remove', $variant) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mt-1.5 text-[12.5px] font-light text-blush underline underline-offset-2">Remove</button>
                                </form>
                            </div>
                        </div>
                        <div class="flex flex-1 justify-start sm:justify-center">
                            <form method="POST" action="{{ route('cart.update', $variant) }}" class="flex items-center border border-line-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" name="quantity" value="{{ $line['qty'] - 1 }}" aria-label="Decrease quantity" class="px-3.5 py-2.5 transition-colors hover:text-blush">−</button>
                                <span class="px-1.5 py-2.5 font-medium">{{ $line['qty'] }}</span>
                                <button type="submit" name="quantity" value="{{ $line['qty'] + 1 }}" aria-label="Increase quantity" @disabled($line['qty'] >= $variant->stock) class="px-3.5 py-2.5 transition-colors hover:text-blush disabled:text-faint">+</button>
                            </form>
                        </div>
                        <div class="text-right text-[16px] font-semibold sm:flex-[0.8]">{{ \App\Models\Product::money($line['total']) }}</div>
                    </div>
                @endforeach

                {{-- Discount code --}}
                @if($summary['coupon'])
                    <form method="POST" action="{{ route('cart.coupon.remove') }}" class="mt-5 flex flex-wrap items-center gap-3">
                        @csrf
                        @method('DELETE')
                        <span class="border border-blush bg-cream-3 px-5 py-3 text-[14px] font-medium text-blush">{{ $summary['coupon']->code }} applied</span>
                        <button type="submit" class="text-[13px] font-light text-muted-2 underline underline-offset-2">Remove code</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('cart.coupon') }}" class="mt-5 flex flex-wrap gap-3">
                        @csrf
                        <input type="text" name="code" placeholder="Discount code" class="flex-none basis-[260px] border border-line-2 px-5 py-3 text-[14px] font-light text-ink placeholder:text-muted outline-none focus:border-blush">
                        <button type="submit" class="border border-ink px-6 py-3 text-[14px] font-medium transition-colors hover:bg-ink hover:text-white">Apply</button>
                    </form>
                @endif
            </div>

            {{-- Summary --}}
            <div class="w-full lg:max-w-[400px] lg:flex-1">
                <div class="bg-cream p-7">
                    <div class="mb-[18px] text-[18px] font-medium">Order Summary</div>
                    <div class="flex justify-between text-[14.5px] font-light leading-[2.2] text-muted-3"><span>Subtotal</span><span>{{ \App\Models\Product::money($summary['subtotal']) }}</span></div>
                    <div class="flex justify-between text-[14.5px] font-light leading-[2.2] text-muted-3">
                        <span>Shipping</span>
                        <span class="{{ $summary['shipping'] > 0 ? '' : 'text-jade' }}">{{ $summary['shipping'] > 0 ? \App\Models\Product::money($summary['shipping']) : 'Free' }}</span>
                    </div>
                    @if($summary['discount'] > 0)
                        <div class="flex justify-between text-[14.5px] font-light leading-[2.2] text-muted-3">
                            <span>Discount ({{ $summary['coupon']->code }})</span>
                            <span class="text-blush">−{{ \App\Models\Product::money($summary['discount']) }}</span>
                        </div>
                    @endif
                    <div class="mt-3.5 flex justify-between border-t border-line-3 pt-4 text-[18px] font-semibold"><span>Total</span><span>{{ \App\Models\Product::money($summary['total']) }}</span></div>
                    <a href="{{ route('checkout') }}" class="mt-5 block bg-ink py-4 text-center text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Checkout</a>
                    <div class="mt-3 text-center text-[13px] font-light text-muted">or <a href="{{ route('listing') }}" class="underline underline-offset-2">continue shopping</a></div>
                </div>
                <div class="mt-4 bg-tan px-5 py-4 text-[13.5px] font-light text-ink">
                    @if($summary['free_shipping'])
                        🎉 You've unlocked <b class="font-medium">free shipping</b>.
                    @else
                        Add <b class="font-medium">{{ \App\Models\Product::money($freeShippingRemainder) }}</b> more to unlock free shipping.
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
