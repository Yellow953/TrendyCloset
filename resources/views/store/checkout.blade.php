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
                <div class="text-[18px] font-medium">Your details</div>
                <div class="mt-3 flex flex-col gap-3">
                    <div>
                        <label for="ship_name" class="tc-field-label">Full name</label>
                        <input id="ship_name" name="ship_name" value="{{ old('ship_name') }}" required autocomplete="name"
                               placeholder="Full name" class="tc-input">
                        @error('ship_name')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ship_phone" class="tc-field-label">Phone number</label>
                        <div class="flex gap-2">
                            <select name="ship_phone_code" required autocomplete="tel-country-code"
                                    aria-label="Phone country code" class="tc-select w-[150px] shrink-0 sm:w-[200px]">
                                @foreach($dialCodes as $dial)
                                    <option value="{{ $dial['dial'] }}" @selected(old('ship_phone_code', $defaultDial) === $dial['dial'])>+{{ $dial['dial'] }} · {{ $dial['name'] }}</option>
                                @endforeach
                            </select>
                            <input id="ship_phone" type="tel" name="ship_phone" value="{{ old('ship_phone') }}" required
                                   autocomplete="tel-national" inputmode="tel" placeholder="76 158 735" class="tc-input">
                        </div>
                        @error('ship_phone_code')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                        @error('ship_phone')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="tc-field-label">Email <span class="font-light normal-case tracking-normal text-muted">(optional)</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                               placeholder="you@example.com" class="tc-input">
                        @error('email')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                </div>
                <label class="mt-2.5 flex items-center gap-2 text-[13px] font-light text-muted-2">
                    <input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in')) class="h-4 w-4 accent-blush">
                    Send me new drops and offers
                </label>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Delivery address</div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="ship_street" class="tc-field-label">Street</label>
                        <input id="ship_street" name="ship_street" value="{{ old('ship_street') }}" required autocomplete="address-line1"
                               placeholder="Street name" class="tc-input">
                        @error('ship_street')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ship_building" class="tc-field-label">Building</label>
                        <input id="ship_building" name="ship_building" value="{{ old('ship_building') }}" autocomplete="address-line2"
                               placeholder="Building name or number" class="tc-input">
                        @error('ship_building')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ship_floor" class="tc-field-label">Floor</label>
                        <input id="ship_floor" name="ship_floor" value="{{ old('ship_floor') }}"
                               placeholder="e.g. 4" class="tc-input">
                        @error('ship_floor')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="ship_details" class="tc-field-label">Address details <span class="font-light normal-case tracking-normal text-muted">(optional)</span></label>
                        <input id="ship_details" name="ship_details" value="{{ old('ship_details') }}"
                               placeholder="Landmark, nearest junction, anything that helps the driver" class="tc-input">
                        @error('ship_details')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ship_city" class="tc-field-label">City / area</label>
                        <input id="ship_city" name="ship_city" value="{{ old('ship_city') }}" required autocomplete="address-level2"
                               placeholder="City / area" class="tc-input">
                        @error('ship_city')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ship_country" class="tc-field-label">Country</label>
                        <select id="ship_country" name="ship_country" required autocomplete="country-name" class="tc-select">
                            @foreach($countries as $country)
                                <option value="{{ $country }}" @selected(old('ship_country', $defaultCountry) === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                        @error('ship_country')<p class="mt-1.5 text-[12.5px] font-normal text-blush">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Delivery</div>
                <div class="flex justify-between rounded-card border border-blush bg-cream-3 p-4 text-[14px] font-normal">
                    <span><b class="font-medium">Standard</b> · 3–5 business days</span>
                    <span class="{{ $summary['shipping'] > 0 ? '' : 'text-jade' }}">{{ $summary['shipping'] > 0 ? \App\Models\Product::money($summary['shipping']) : 'Free' }}</span>
                </div>
            </div>

            <div>
                <div class="mb-3 text-[18px] font-medium">Order notes</div>
                <textarea name="notes" rows="3" placeholder="Anything we should know — delivery instructions, gift wrapping…"
                          class="tc-input resize-y">{{ old('notes') }}</textarea>
            </div>

            <div class="rounded-card border border-line-2 bg-cream-3 p-4 text-[13.5px] leading-relaxed font-light text-muted-3">
                <b class="font-medium text-ink">Payment</b> is arranged after you place your order — we will email you at the
                address above to confirm your pieces and settle up. No card details are taken here.
            </div>

            <button type="submit" class="tc-btn-dark text-[15px]">
                Place order · {{ \App\Models\Product::money($summary['total']) }}
            </button>
        </form>

        {{-- Summary --}}
        <div class="w-full lg:max-w-[420px] lg:flex-1">
            <div class="tc-panel-quiet">
                @foreach($lines as $line)
                    @php($variant = $line['variant'])
                    @php($product = $variant->product)
                    <div class="flex items-center gap-3.5 pb-4">
                        <div class="relative">
                            <div class="tc-media h-[70px] w-[58px] rounded-field bg-white">
                                <x-img :src="$product->image_url" :alt="$product->name" sizes="58px" class="h-full w-full object-cover" />
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
