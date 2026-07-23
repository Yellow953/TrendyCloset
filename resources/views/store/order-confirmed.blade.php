@extends('layouts.storefront')

@section('content')
    <div class="mx-auto max-w-[720px] px-5 py-16 md:px-10 md:py-24">

        <div class="text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-jade/30 bg-jade/8 text-[22px] text-jade">✓</span>
            <h1 class="mt-7 font-serif text-[36px] leading-tight font-normal md:text-[44px]">Thank you</h1>
            <span class="tc-heading-rule"></span>
            <p class="mx-auto mt-5 max-w-[46ch] text-[14.5px] leading-relaxed font-light text-muted-2">
                Your order is with us. We have sent nothing yet — we will email
                <b class="font-medium text-ink">{{ $order->email }}</b> to confirm your pieces and arrange payment.
            </p>
        </div>

        <div class="mt-12 border border-line-2">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-2 bg-cream-3 px-6 py-4">
                <div>
                    <div class="text-[11px] font-medium tracking-[0.18em] text-muted uppercase">Order</div>
                    <div class="mt-1 font-display text-[17px]">{{ $order->order_number }}</div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] font-medium tracking-[0.18em] text-muted uppercase">Placed</div>
                    <div class="mt-1 text-[14px] font-light">{{ $order->created_at->format('j M Y') }}</div>
                </div>
            </div>

            <div class="px-6 py-5">
                @foreach($order->items as $item)
                    <div class="flex items-start justify-between gap-4 border-b border-line py-3 last:border-0">
                        <div>
                            <div class="text-[14.5px] font-normal">{{ $item->product_name }}</div>
                            <div class="mt-0.5 text-[12.5px] font-light text-muted">
                                {{ collect([$item->variant_size ? 'Size '.$item->variant_size : null, $item->variant_color])->filter()->implode(' · ') }}
                                · Qty {{ $item->quantity }}
                            </div>
                        </div>
                        <div class="shrink-0 text-[14px] font-medium">{{ \App\Models\Product::money($item->line_total) }}</div>
                    </div>
                @endforeach

                <div class="mt-4 border-t border-line-3 pt-4">
                    <div class="flex justify-between text-[14px] leading-[2] font-light text-muted-3"><span>Subtotal</span><span>{{ \App\Models\Product::money($order->subtotal) }}</span></div>
                    @if((float) $order->discount_total > 0)
                        <div class="flex justify-between text-[14px] leading-[2] font-light text-muted-3"><span>Discount</span><span class="text-blush">−{{ \App\Models\Product::money($order->discount_total) }}</span></div>
                    @endif
                    <div class="flex justify-between text-[14px] leading-[2] font-light text-muted-3">
                        <span>Shipping</span>
                        <span class="{{ (float) $order->shipping_total > 0 ? '' : 'text-jade' }}">{{ (float) $order->shipping_total > 0 ? \App\Models\Product::money($order->shipping_total) : 'Free' }}</span>
                    </div>
                    <div class="mt-2.5 flex justify-between text-[18px] font-semibold"><span>Total</span><span>{{ \App\Models\Product::money($order->grand_total) }}</span></div>
                </div>
            </div>

            <div class="border-t border-line-2 px-6 py-5">
                <div class="text-[11px] font-medium tracking-[0.18em] text-muted uppercase">Shipping to</div>
                <address class="mt-2 text-[14px] leading-relaxed font-light text-muted-3 not-italic">
                    @foreach($order->addressLines() as $line)
                        {{ $line }}<br>
                    @endforeach
                </address>
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('listing') }}" class="tc-btn-outline">Continue shopping</a>
        </div>
    </div>
@endsection
