@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)
@section('heading', $order->order_number)
@section('subheading', 'Placed '.$order->created_at->format('j F Y \a\t H:i').' · '.$order->created_at->diffForHumans())

@section('breadcrumb')
    <a href="{{ route('admin.orders.index') }}" class="hover:text-slate-900">Orders</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $order->order_number }}</span>
@endsection

@section('actions')
    <x-admin.status :status="$order->status" />
    <button type="button" data-modal-open="status" class="ad-btn-primary">Change status</button>
    <a href="mailto:{{ $order->email }}?subject={{ rawurlencode('Your Trendy Closet order '.$order->order_number) }}" class="ad-btn">Email customer</a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_360px]">

        {{-- Lines ---------------------------------------------------------- --}}
        <div class="flex flex-col gap-5">
            <div class="ad-card">
                <div class="ad-card-head">
                    <div>
                        <div class="ad-card-title">{{ $order->items->count() }} {{ Str::plural('line', $order->items->count()) }}</div>
                        <p class="mt-0.5 text-[12px] font-normal text-slate-400">
                            Every line is a snapshot of the piece as it was bought — editing the catalogue never rewrites it.
                        </p>
                    </div>
                    <span class="ad-badge ad-badge-neutral"><span class="ad-figure">{{ $order->quantity }}</span> garments</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="ad-table">
                        <thead>
                            <tr><th>Piece</th><th>SKU</th><th class="text-right">Unit</th><th class="text-right">Qty</th><th class="text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-normal">
                                            @if($item->variant?->product)
                                                <a href="{{ route('admin.products.edit', $item->variant->product) }}" class="hover:text-slate-900">{{ $item->product_name }}</a>
                                            @else
                                                {{ $item->product_name }}
                                                <span class="ml-1.5 text-[11px] font-normal text-slate-400">(no longer in the catalogue)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[11.5px] font-normal text-slate-400">
                                            {{ collect([$item->variant_size ? 'Size '.$item->variant_size : null, $item->variant_color])->filter()->implode(' · ') ?: 'One size' }}
                                        </div>
                                    </td>
                                    <td class="ad-figure text-[12px] font-normal text-slate-400">{{ $item->sku ?: '—' }}</td>
                                    <td class="ad-figure text-right font-normal text-slate-600">{{ \App\Models\Product::money($item->unit_price) }}</td>
                                    <td class="ad-figure text-right">{{ $item->quantity }}</td>
                                    <td class="ad-figure text-right font-medium">{{ \App\Models\Product::money($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-5 py-4">
                    <div class="ml-auto w-full max-w-[300px]">
                        <div class="flex justify-between py-1 text-[13px] font-normal text-slate-600">
                            <span>Subtotal</span><span class="ad-figure">{{ \App\Models\Product::money($order->subtotal) }}</span>
                        </div>
                        @if((float) $order->discount_total > 0)
                            <div class="flex justify-between py-1 text-[13px] font-normal text-slate-600">
                                <span>Discount @if($order->coupon)<span class="ad-figure text-slate-900">{{ $order->coupon->code }}</span>@endif</span>
                                <span class="ad-figure text-slate-900">−{{ \App\Models\Product::money($order->discount_total) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-1 text-[13px] font-normal text-slate-600">
                            <span>Shipping</span>
                            <span class="ad-figure {{ (float) $order->shipping_total > 0 ? '' : 'text-emerald-600' }}">
                                {{ (float) $order->shipping_total > 0 ? \App\Models\Product::money($order->shipping_total) : 'Free' }}
                            </span>
                        </div>
                        <div class="mt-2 flex justify-between border-t border-slate-100 pt-2.5 text-[16px] font-semibold">
                            <span>Total</span><span class="ad-figure">{{ \App\Models\Product::money($order->grand_total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Internal notes --}}
            <div class="ad-card">
                <div class="ad-card-head"><div class="ad-card-title">Notes</div></div>
                <form method="POST" action="{{ route('admin.orders.notes', $order) }}" class="px-5 py-5">
                    @csrf @method('PATCH')
                    <textarea name="notes" rows="4" class="ad-input resize-y"
                              placeholder="Anything worth remembering about this order — what the customer asked for, what you promised…">{{ old('notes', $order->notes) }}</textarea>
                    <p class="ad-hint">Whatever the shopper wrote at checkout starts here. Back-office only — the customer never sees this.</p>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="ad-btn-primary">Save notes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar -------------------------------------------------------- --}}
        <div class="flex flex-col gap-5">
            <div class="ad-card">
                <div class="ad-card-head"><div class="ad-card-title">Customer</div></div>
                <div class="px-5 py-5">
                    @if($order->customer)
                        <a href="{{ route('admin.customers.show', $order->customer) }}" class="flex items-center gap-3 group">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[14px] font-medium text-slate-900">
                                {{ strtoupper(mb_substr($order->customer->name ?: $order->ship_name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-[14px] font-medium group-hover:text-slate-900">{{ $order->customer->name }}</div>
                                <div class="truncate text-[12px] font-normal text-slate-400">{{ $order->customer->email }}</div>
                            </div>
                        </a>

                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
                            <div>
                                <div class="ad-eyebrow">Orders</div>
                                <div class="ad-figure mt-1 text-[17px] font-medium">{{ $order->customer->orders()->count() }}</div>
                            </div>
                            <div>
                                <div class="ad-eyebrow">Lifetime</div>
                                <div class="ad-figure mt-1 text-[17px] font-medium">{{ \App\Models\Product::money($order->customer->lifetimeValue()) }}</div>
                            </div>
                        </div>
                    @else
                        <p class="text-[13px] font-normal text-slate-500">
                            The customer record for this order has been deleted. The order itself is untouched —
                            <span class="text-slate-800">{{ $order->email }}</span> is what it was placed with.
                        </p>
                    @endif
                </div>
            </div>

            <div class="ad-card">
                <div class="ad-card-head"><div class="ad-card-title">Ship to</div></div>
                <div class="px-5 py-5">
                    <address class="text-[13.5px] leading-relaxed font-normal text-slate-600 not-italic">
                        @foreach($order->addressLines() as $line)
                            {{ $line }}<br>
                        @endforeach
                    </address>

                    <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 text-[13px]">
                        <div class="flex justify-between gap-3">
                            <span class="font-normal text-slate-400">Email</span>
                            <a href="mailto:{{ $order->email }}" class="truncate font-normal hover:text-slate-900">{{ $order->email }}</a>
                        </div>
                        @if($order->ship_phone)
                            <div class="flex justify-between gap-3">
                                <span class="font-normal text-slate-400">Phone</span>
                                <a href="tel:{{ $order->ship_phone }}" class="font-normal hover:text-slate-900">{{ $order->ship_phone }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ad-card">
                <div class="ad-card-head"><div class="ad-card-title">Timeline</div></div>
                <div class="flex flex-col gap-3.5 px-5 py-5 text-[13px]">
                    <div class="flex justify-between gap-3">
                        <span class="font-normal text-slate-400">Placed</span>
                        <span class="font-normal">{{ $order->created_at->format('j M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="font-normal text-slate-400">Last updated</span>
                        <span class="font-normal">{{ $order->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-normal text-slate-400">Status</span>
                        <x-admin.status :status="$order->status" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <x-admin.modal id="status" title="Change status" :subtitle="'Order '.$order->order_number">
        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
            @csrf @method('PATCH')

            <div class="px-6 py-5">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($statuses as $status)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2.5 transition-colors {{ $order->status === $status ? 'border-slate-900 bg-slate-900/6' : 'border-slate-200 hover:border-slate-900' }}">
                            <input type="radio" name="status" value="{{ $status->value }}" @checked($order->status === $status) class="h-3.5 w-3.5 accent-slate-900">
                            <span class="text-[13px] font-normal">{{ $status->label() }}</span>
                        </label>
                    @endforeach
                </div>

                <p class="ad-hint">
                    Moving an order to Cancelled or Refunded puts its garments back on the rail;
                    moving it back off takes them again.
                </p>
            </div>

            <div class="flex justify-end gap-2.5 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button type="button" data-modal-close class="ad-btn">Cancel</button>
                <button type="submit" class="ad-btn-primary">Update status</button>
            </div>
        </form>
    </x-admin.modal>
@endsection
