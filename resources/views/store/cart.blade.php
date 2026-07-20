@extends('layouts.storefront')

@section('title', 'Your Bag — Trendy Closet')

@section('content')
    <div class="px-5 md:px-10 pb-3 pt-9"><h1 class="text-[32px] font-normal">Your Bag <span class="text-[18px] font-light text-muted">(3 items)</span></h1></div>

    <div class="flex flex-col gap-10 px-5 md:px-10 pb-12 pt-5 lg:flex-row">
        {{-- Items --}}
        <div class="lg:flex-[1.6]">
            <div class="hidden border-b border-line pb-3 text-[12px] font-medium tracking-[0.1em] text-muted sm:flex">
                <div class="flex-[2.2]">PRODUCT</div><div class="flex-1 text-center">QTY</div><div class="flex-[0.8] text-right">TOTAL</div>
            </div>
            @foreach($cart as $i)
                <div class="flex flex-wrap items-center gap-y-4 border-b border-line py-5">
                    <div class="flex w-full items-center gap-4 sm:w-auto sm:flex-[2.2]">
                        <div class="h-[92px] w-[76px] flex-none overflow-hidden bg-cream"><img src="{{ $i['img'] }}" alt="{{ $i['name'] }}" class="h-full w-full object-cover"></div>
                        <div>
                            <div class="text-[15.5px] font-normal">{{ $i['name'] }}</div>
                            <div class="mt-1 text-[13px] font-light text-muted">{{ $i['meta'] }}</div>
                            <div class="mt-1.5 text-[12.5px] font-light text-blush underline underline-offset-2">Remove</div>
                        </div>
                    </div>
                    <div class="flex flex-1 justify-start sm:justify-center">
                        <div class="flex items-center border border-line-2"><div class="px-3.5 py-2.5">−</div><div class="px-1.5 py-2.5 font-medium">{{ $i['qty'] }}</div><div class="px-3.5 py-2.5">+</div></div>
                    </div>
                    <div class="text-right text-[16px] font-semibold sm:flex-[0.8]">{{ $i['total'] }}</div>
                </div>
            @endforeach
            <div class="mt-5 flex flex-wrap gap-3">
                <input type="text" placeholder="Discount code" class="flex-none basis-[260px] border border-line-2 px-5 py-3 text-[14px] font-light text-ink placeholder:text-muted outline-none focus:border-blush">
                <button class="border border-ink px-6 py-3 text-[14px] font-medium transition-colors hover:bg-ink hover:text-white">Apply</button>
            </div>
        </div>

        {{-- Summary --}}
        <div class="w-full lg:max-w-[400px] lg:flex-1">
            <div class="bg-cream p-7">
                <div class="mb-[18px] text-[18px] font-medium">Order Summary</div>
                <div class="flex justify-between text-[14.5px] font-light leading-[2.2] text-muted-3"><span>Subtotal</span><span>$134.00</span></div>
                <div class="flex justify-between text-[14.5px] font-light leading-[2.2] text-muted-3"><span>Shipping</span><span class="text-jade">Free</span></div>
                <div class="flex justify-between text-[14.5px] font-light leading-[2.2] text-muted-3"><span>Discount (WELCOME10)</span><span class="text-blush">−$13.40</span></div>
                <div class="mt-3.5 flex justify-between border-t border-line-3 pt-4 text-[18px] font-semibold"><span>Total</span><span>$120.60</span></div>
                <a href="{{ route('checkout') }}" class="mt-5 block bg-ink py-4 text-center text-[14px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Checkout</a>
                <div class="mt-3 text-center text-[13px] font-light text-muted">or <a href="{{ route('listing') }}" class="underline underline-offset-2">continue shopping</a></div>
            </div>
            <div class="mt-4 bg-tan px-5 py-4 text-[13.5px] font-light text-ink">🎉 You've unlocked <b class="font-medium">free shipping</b> — orders over $150 also get a free tote.</div>
        </div>
    </div>
@endsection
