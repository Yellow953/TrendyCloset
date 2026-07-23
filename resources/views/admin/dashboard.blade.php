@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Good '.(now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening')).', '.explode(' ', auth()->user()->name)[0])
@section('subheading', 'Here is how the shop is trading. Figures cover the last 30 days unless noted.')

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="ad-btn-primary">＋ New product</a>
    <a href="{{ route('admin.orders.index') }}" class="ad-btn">All orders</a>
@endsection

@section('content')
    {{-- KPI row --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpis as $kpi)
            <div class="ad-card p-5">
                <div class="ad-eyebrow">{{ $kpi['label'] }}</div>
                <div class="mt-2.5 flex items-baseline gap-2.5">
                    <span class="ad-figure text-[27px] leading-none font-medium">{{ $kpi['value'] }}</span>
                    @if($kpi['delta'] !== null)
                        <span class="ad-badge {{ $kpi['delta'] >= 0 ? 'ad-badge-good' : 'ad-badge-bad' }}">
                            {{ $kpi['delta'] >= 0 ? '↑' : '↓' }} {{ abs($kpi['delta']) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-2.5 text-[12px] leading-relaxed font-normal text-slate-400">{{ $kpi['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- Revenue chart --}}
        @php
            $peak = max($revenueSeries->max('value'), 1);
            $seriesTotal = $revenueSeries->sum('value');
        @endphp
        <div class="ad-card xl:col-span-2">
            <div class="ad-card-head">
                <div>
                    <div class="ad-card-title">Revenue, last 14 days</div>
                    <p class="mt-0.5 text-[12px] font-normal text-slate-400">
                        <span class="ad-figure text-slate-800">{{ \App\Models\Product::money($seriesTotal) }}</span> taken across the fortnight
                    </p>
                </div>
                <span class="ad-badge ad-badge-neutral">Peak {{ \App\Models\Product::money($peak) }}</span>
            </div>

            <div class="px-5 py-6">
                <div class="flex h-[190px] items-end gap-1.5">
                    @foreach($revenueSeries as $i => $point)
                        <div class="group relative flex flex-1 flex-col items-center justify-end self-stretch">
                            {{-- Tooltip --}}
                            <div class="pointer-events-none absolute bottom-full z-10 mb-2 hidden whitespace-nowrap rounded-md bg-slate-800 px-2.5 py-1.5 text-[11px] text-white group-hover:block">
                                {{ $point['label'] }} · <span class="ad-figure">{{ \App\Models\Product::money($point['value']) }}</span>
                            </div>

                            <div class="ad-bar w-full rounded-t-[3px] {{ $point['value'] > 0 ? 'bg-slate-900 group-hover:bg-slate-800' : 'bg-slate-100' }} transition-colors"
                                 style="height: {{ $point['value'] > 0 ? max(round(($point['value'] / $peak) * 100), 4) : 2 }}%; animation-delay: {{ $i * 35 }}ms"></div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex gap-1.5 border-t border-slate-100 pt-2.5">
                    @foreach($revenueSeries as $point)
                        <div class="flex-1 text-center text-[10px] font-normal text-slate-400">{{ $point['day'][0] }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Order pipeline --}}
        <div class="ad-card">
            <div class="ad-card-head">
                <div class="ad-card-title">Order pipeline</div>
                <a href="{{ route('admin.orders.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">View all</a>
            </div>

            @php $pipelineTotal = max(array_sum($statusCounts), 1); @endphp
            <div class="flex flex-col gap-3.5 px-5 py-5">
                @foreach(\App\Enums\OrderStatus::cases() as $status)
                    @php $count = $statusCounts[$status->value] ?? 0; @endphp
                    <a href="{{ route('admin.orders.index', ['status' => $status->value]) }}" class="group block">
                        <div class="flex items-center justify-between text-[12.5px]">
                            <span class="font-normal text-slate-600 group-hover:text-slate-800">{{ $status->label() }}</span>
                            <span class="ad-figure font-medium">{{ $count }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-slate-900 transition-all" style="width: {{ round(($count / $pipelineTotal) * 100) }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- Recent orders --}}
        <div class="ad-card xl:col-span-2">
            <div class="ad-card-head">
                <div class="ad-card-title">Latest orders</div>
                <a href="{{ route('admin.orders.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">View all</a>
            </div>

            @if($recentOrders->isEmpty())
                <x-admin.empty icon="❏" title="No orders yet"
                               body="When someone checks out, their order lands here as pending." />
            @else
                <div class="overflow-x-auto">
                    <table class="ad-table">
                        <thead>
                            <tr><th>Order</th><th>Customer</th><th>Placed</th><th>Status</th><th class="text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="ad-figure font-medium hover:text-slate-900">{{ $order->order_number }}</a></td>
                                    <td class="max-w-[180px] truncate font-normal text-slate-600">{{ $order->customer?->name ?? $order->ship_name }}</td>
                                    <td class="font-normal whitespace-nowrap text-slate-400">{{ $order->created_at->diffForHumans(short: true) }}</td>
                                    <td><x-admin.status :status="$order->status" /></td>
                                    <td class="ad-figure text-right font-medium">{{ \App\Models\Product::money($order->grand_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Low stock --}}
        <div class="ad-card">
            <div class="ad-card-head">
                <div class="ad-card-title">Running out</div>
                <span class="ad-badge ad-badge-warn">≤ 3 left</span>
            </div>

            @if($lowStock->isEmpty())
                <x-admin.empty icon="✓" title="Everything is stocked" body="No active size is down to its last few." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($lowStock as $variant)
                        <a href="{{ route('admin.products.edit', $variant->product) }}"
                           class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-[13px] font-normal">{{ $variant->product->name }}</div>
                                <div class="mt-0.5 text-[11.5px] font-normal text-slate-400">{{ $variant->label ?: 'One size' }}</div>
                            </div>
                            <span class="ad-badge {{ $variant->stock === 0 ? 'ad-badge-bad' : 'ad-badge-warn' }}">
                                <span class="ad-figure">{{ $variant->stock }}</span> left
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- Top products --}}
        <div class="ad-card xl:col-span-2">
            <div class="ad-card-head">
                <div class="ad-card-title">What is selling</div>
                <span class="text-[12px] font-normal text-slate-400">Units sold, then views · 30 days</span>
            </div>

            @if($topProducts->isEmpty())
                <x-admin.empty icon="⬚" title="No products yet"
                               body="Add your first piece and its engagement will show up here.">
                    <a href="{{ route('admin.products.create') }}" class="ad-btn-primary">＋ New product</a>
                </x-admin.empty>
            @else
                <div class="overflow-x-auto">
                    <table class="ad-table">
                        <thead>
                            <tr><th>Piece</th><th class="text-right">Sold</th><th class="text-right">Views</th><th class="text-right">Added to bag</th><th class="text-right">Saved</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="block hover:text-slate-900">
                                            <span class="font-normal">{{ $product->name }}</span>
                                            <span class="mt-0.5 block text-[11.5px] font-normal text-slate-400">{{ $product->category?->name }}</span>
                                        </a>
                                    </td>
                                    <td class="ad-figure text-right font-medium">{{ (int) $product->units_sold }}</td>
                                    <td class="ad-figure text-right font-normal text-slate-600">{{ number_format($product->views_count) }}</td>
                                    <td class="ad-figure text-right font-normal text-slate-600">{{ number_format($product->add_to_cart_count) }}</td>
                                    <td class="ad-figure text-right font-normal text-slate-600">{{ number_format($product->favorites_count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Unread enquiries --}}
        <div class="ad-card">
            <div class="ad-card-head">
                <div class="ad-card-title">Unread enquiries</div>
                <a href="{{ route('admin.messages.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">Inbox</a>
            </div>

            @if($unreadMessages->isEmpty())
                <x-admin.empty icon="✉" title="Inbox clear" body="Every contact-form message has been read." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($unreadMessages as $message)
                        <a href="{{ route('admin.messages.show', $message) }}" class="block px-5 py-3.5 transition-colors hover:bg-slate-50">
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="truncate text-[13px] font-medium">{{ $message->name }}</span>
                                <span class="shrink-0 text-[11px] font-normal text-slate-400">{{ $message->created_at->diffForHumans(short: true) }}</span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-[12.5px] leading-relaxed font-normal text-slate-500">{{ $message->subject ?: $message->message }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
