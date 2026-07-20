@extends('layouts.storefront')

@section('title', 'Checkout — Trendy Closet')

@section('content')
    {{-- Checkout progress sub-header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 md:px-10 py-4">
        <div class="flex items-center gap-2.5 text-[13px] font-normal text-muted">
            <span class="font-medium text-ink">Bag</span><span>—</span><span class="font-medium text-blush">Information</span><span>—</span><span>Shipping</span><span>—</span><span>Payment</span>
        </div>
        <div class="text-[13px] font-light text-jade">🔒 Secure checkout</div>
    </div>

    <div class="flex flex-col gap-12 px-5 md:px-10 pb-12 pt-9 lg:flex-row">
        {{-- Form --}}
        <div class="flex flex-col gap-[26px] lg:flex-[1.5]">
            <div>
                <div class="flex items-baseline justify-between"><div class="text-[18px] font-medium">Contact</div><div class="text-[13px] font-light text-muted">Have an account? <a href="{{ route('login') }}" class="text-blush underline underline-offset-2">Log in</a></div></div>
                <input type="email" placeholder="Email address" class="tc-input mt-3">
                <label class="mt-2.5 flex items-center gap-2 text-[13px] font-light text-muted-2"><span class="inline-block h-4 w-4 border border-line-2"></span>Email me new drops and offers</label>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Shipping address</div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input placeholder="First name" class="tc-input">
                    <input placeholder="Last name" class="tc-input">
                    <input placeholder="Street address" class="tc-input sm:col-span-2">
                    <input placeholder="City" class="tc-input">
                    <input placeholder="Postal code" class="tc-input">
                    <input placeholder="Country ▾" class="tc-input">
                    <input placeholder="Phone" class="tc-input">
                </div>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Delivery</div>
                <div class="flex justify-between border border-blush bg-cream-3 p-4 text-[14px] font-normal"><span><b class="font-medium">Standard</b> · 3–5 business days</span><span class="text-jade">Free</span></div>
                <div class="flex justify-between border border-t-0 border-line-2 p-4 text-[14px] font-normal text-muted-2"><span>Express · 1–2 business days</span><span>$9.00</span></div>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Payment</div>
                <div class="border border-blush bg-cream-3 p-4 text-[14px] font-normal">Credit / debit card</div>
                <div class="grid grid-cols-1 gap-3 border border-t-0 border-line-2 p-4 sm:grid-cols-[2fr_1fr_1fr]">
                    <input placeholder="Card number" class="border border-line-2 px-3.5 py-3 text-[13.5px] font-light text-ink placeholder:text-muted outline-none focus:border-blush">
                    <input placeholder="MM / YY" class="border border-line-2 px-3.5 py-3 text-[13.5px] font-light text-ink placeholder:text-muted outline-none focus:border-blush">
                    <input placeholder="CVC" class="border border-line-2 px-3.5 py-3 text-[13.5px] font-light text-ink placeholder:text-muted outline-none focus:border-blush">
                </div>
                <div class="border border-t-0 border-line-2 p-4 text-[14px] font-normal text-muted-2">PayPal</div>
                <div class="border border-t-0 border-line-2 p-4 text-[14px] font-normal text-muted-2">Apple Pay</div>
            </div>

            <button class="bg-ink py-4 text-center text-[15px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">Pay $120.60</button>
        </div>

        {{-- Summary --}}
        <div class="w-full lg:max-w-[420px] lg:flex-1">
            <div class="bg-cream p-7">
                @foreach($cart as $i)
                    <div class="flex items-center gap-3.5 pb-4">
                        <div class="relative">
                            <div class="h-[70px] w-[58px] overflow-hidden rounded-md bg-white"><img src="{{ $i['img'] }}" alt="{{ $i['name'] }}" class="h-full w-full object-cover"></div>
                            <div class="pointer-events-none absolute -right-[7px] -top-[7px] flex h-5 w-5 items-center justify-center rounded-full bg-blush text-[11px] font-medium text-white">{{ $i['qty'] }}</div>
                        </div>
                        <div class="flex-1"><div class="text-[14px] font-normal">{{ $i['name'] }}</div><div class="text-[12.5px] font-light text-muted">{{ $i['meta'] }}</div></div>
                        <div class="text-[14px] font-medium">{{ $i['total'] }}</div>
                    </div>
                @endforeach
                <div class="border-t border-line-3 pt-4">
                    <div class="flex justify-between text-[14px] font-light leading-[2] text-muted-3"><span>Subtotal</span><span>$134.00</span></div>
                    <div class="flex justify-between text-[14px] font-light leading-[2] text-muted-3"><span>Discount</span><span class="text-blush">−$13.40</span></div>
                    <div class="flex justify-between text-[14px] font-light leading-[2] text-muted-3"><span>Shipping</span><span class="text-jade">Free</span></div>
                    <div class="mt-2.5 flex justify-between text-[18px] font-semibold"><span>Total</span><span>$120.60</span></div>
                </div>
            </div>
            <div class="mt-4 flex justify-center gap-[18px] text-[12.5px] font-light text-muted"><span>🔒 SSL encrypted</span><span>↩ 30-day returns</span><span>✓ Buyer protection</span></div>
        </div>
    </div>
@endsection
