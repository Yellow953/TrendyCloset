@extends('layouts.storefront')

@section('content')
    {{-- Checkout progress sub-header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 md:px-10 py-4">
        <div class="flex items-center gap-2.5 text-[13px] font-normal text-muted">
            <a href="{{ route('cart') }}" class="font-medium text-ink hover:text-blush">Bag</a><span>—</span><span class="font-medium text-blush">Information</span><span>—</span><span>Shipping</span><span>—</span><span>Payment</span>
        </div>
        <div class="text-[13px] font-light text-jade">🔒 Secure checkout</div>
    </div>

    <div class="flex flex-col gap-12 px-5 md:px-10 pb-12 pt-9 lg:flex-row">
        {{-- The real thing: this writes an order. There is no payment gateway,
             so no card details are collected — the order lands as `pending` and
             the back office arranges payment. --}}
        <form method="POST" action="{{ route('checkout.place') }}" class="flex flex-col gap-[26px] lg:flex-[1.5]">
            @csrf

            <div>
                <div class="text-[18px] font-medium">Contact</div>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email address" class="tc-input mt-3">
                @error('email')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                <label class="mt-2.5 flex items-center gap-2 text-[13px] font-light text-muted-2">
                    <input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in')) class="h-4 w-4 accent-blush">
                    Email me new drops and offers
                </label>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Shipping address</div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach([
                        ['ship_name', 'Full name', true, 'sm:col-span-2'],
                        ['ship_line1', 'Street address', true, 'sm:col-span-2'],
                        ['ship_line2', 'Apartment, suite (optional)', false, 'sm:col-span-2'],
                        ['ship_city', 'City', true, ''],
                        ['ship_postcode', 'Postal code', false, ''],
                        ['ship_region', 'Region / state (optional)', false, ''],
                        ['ship_country', 'Country', true, ''],
                        ['ship_phone', 'Phone (optional)', false, 'sm:col-span-2'],
                    ] as [$field, $placeholder, $required, $span])
                        <div class="{{ $span }}">
                            <input name="{{ $field }}" value="{{ old($field) }}" placeholder="{{ $placeholder }}"
                                   @required($required) class="tc-input">
                            @error($field)<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Delivery</div>
                <div class="flex justify-between border border-blush bg-cream-3 p-4 text-[14px] font-normal">
                    <span><b class="font-medium">Standard</b> · 3–5 business days</span>
                    <span class="{{ $summary['shipping'] > 0 ? '' : 'text-jade' }}">{{ $summary['shipping'] > 0 ? \App\Models\Product::money($summary['shipping']) : 'Free' }}</span>
                </div>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Order notes</div>
                <textarea name="notes" rows="3" placeholder="Anything we should know — delivery instructions, gift wrapping…"
                          class="tc-input resize-y">{{ old('notes') }}</textarea>
            </div>

            <div class="border border-line-2 bg-cream-3 p-4 text-[13.5px] leading-relaxed font-light text-muted-3">
                <b class="font-medium text-ink">Payment</b> is arranged after you place your order — we will email you at the
                address above to confirm your pieces and settle up. No card details are taken here.
            </div>

            <button type="submit" class="bg-ink py-4 text-center text-[15px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush">
                Place order · {{ \App\Models\Product::money($summary['total']) }}
            </button>
        </form>

        {{-- Summary --}}
        <div class="w-full lg:max-w-[420px] lg:flex-1">
            <div class="bg-cream p-7">
                @foreach($lines as $line)
                    @php($variant = $line['variant'])
                    @php($product = $variant->product)
                    <div class="flex items-center gap-3.5 pb-4">
                        <div class="relative">
                            <div class="h-[70px] w-[58px] overflow-hidden rounded-md bg-white">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="pointer-events-none absolute -right-[7px] -top-[7px] flex h-5 w-5 items-center justify-center rounded-full bg-blush text-[11px] font-medium text-white">{{ $line['qty'] }}</div>
                        </div>
                        <div class="flex-1"><div class="text-[14px] font-normal">{{ $product->name }}</div><div class="text-[12.5px] font-light text-muted">{{ $variant->label }}</div></div>
                        <div class="text-[14px] font-medium">{{ \App\Models\Product::money($line['total']) }}</div>
                    </div>
                @endforeach
                <div class="border-t border-line-3 pt-4">
                    <div class="flex justify-between text-[14px] font-light leading-[2] text-muted-3"><span>Subtotal</span><span>{{ \App\Models\Product::money($summary['subtotal']) }}</span></div>
                    @if($summary['discount'] > 0)
                        <div class="flex justify-between text-[14px] font-light leading-[2] text-muted-3"><span>Discount ({{ $summary['coupon']->code }})</span><span class="text-blush">−{{ \App\Models\Product::money($summary['discount']) }}</span></div>
                    @endif
                    <div class="flex justify-between text-[14px] font-light leading-[2] text-muted-3">
                        <span>Shipping</span>
                        <span class="{{ $summary['shipping'] > 0 ? '' : 'text-jade' }}">{{ $summary['shipping'] > 0 ? \App\Models\Product::money($summary['shipping']) : 'Free' }}</span>
                    </div>
                    <div class="mt-2.5 flex justify-between text-[18px] font-semibold"><span>Total</span><span>{{ \App\Models\Product::money($summary['total']) }}</span></div>
                </div>
            </div>
            <div class="mt-4 flex justify-center gap-[18px] text-[12.5px] font-light text-muted"><span>🔒 SSL encrypted</span><span>↩ 30-day returns</span><span>✓ Buyer protection</span></div>
        </div>
    </div>
@endsection
