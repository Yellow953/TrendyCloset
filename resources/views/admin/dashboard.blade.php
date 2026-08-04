@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Good '.(now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening')).', '.explode(' ', auth()->user()->name)[0])
@section('subheading', 'How the shop is trading. Figures cover the last 30 days unless noted.')

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="ad-btn-primary">＋ New product</a>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.analytics') }}" class="ad-btn">Analytics</a>
    @endif
@endsection

@section('content')
    {{-- Four figures, then one chart, then what needs doing. Anything that
         invites a question rather than an action lives on Analytics. --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach($kpis as $kpi)
            <div class="ad-card p-5">
                <div class="ad-eyebrow">{{ $kpi['label'] }}</div>
                <div class="mt-2.5 flex flex-wrap items-baseline gap-x-2.5 gap-y-1.5">
                    <span class="ad-figure text-[24px] leading-none font-medium">{{ $kpi['value'] }}</span>
                    @if($kpi['delta'] !== null)
                        <span class="ad-badge {{ $kpi['delta'] >= 0 ? 'ad-badge-good' : 'ad-badge-bad' }}">
                            {{ $kpi['delta'] >= 0 ? '↑' : '↓' }} {{ abs($kpi['delta']) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-2.5 text-[11.5px] leading-relaxed font-normal text-slate-400">{{ $kpi['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- The funnel: three series, one shared scale --}}
    <div class="ad-card mt-5">
        <div class="ad-card-head">
            <div class="ad-card-title">Views, bags and orders</div>

            {{-- Plain links, so the period survives a reload and a bookmark. --}}
            <div class="flex items-center gap-1 rounded-lg bg-slate-100 p-1">
                @foreach(\App\Http\Controllers\Admin\DashboardController::TRENDS as $key => $option)
                    <a href="{{ route('admin.dashboard', $key === array_key_first(\App\Http\Controllers\Admin\DashboardController::TRENDS) ? [] : ['trend' => $key]) }}"
                       class="rounded-md px-2.5 py-1 text-[12px] font-medium transition-colors
                              {{ $trend === $key ? 'bg-white text-slate-900 shadow-[0_1px_2px_rgba(15,23,42,.08)]' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ $option['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="px-5 py-6">
            <x-admin.line-chart :lines="$funnel['lines']"
                                :buckets="$funnel['buckets']"
                                :partial-label="$trend === 'today' ? 'this hour so far' : 'today so far'" />
        </div>

        <div class="flex flex-wrap gap-x-10 gap-y-3 border-t border-slate-100 px-5 py-3.5">
            @foreach($funnel['steps'] as $step)
                <div class="flex items-baseline gap-2">
                    <span class="text-[12px] font-normal text-slate-400">{{ $step['label'] }}</span>
                    <span class="ad-figure text-[13.5px] font-semibold text-slate-800">
                        {{ $step['rate'] !== null ? $step['rate'].'%' : '—' }}
                    </span>
                    <span class="ad-figure text-[11px] font-normal text-slate-400">{{ $step['hint'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- What needs doing --}}
    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

        <div class="ad-card">
            <div class="ad-card-head">
                <div class="ad-card-title">Latest orders</div>
                <a href="{{ route('admin.orders.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">View all</a>
            </div>

            @if($recentOrders->isEmpty())
                <x-admin.empty icon="orders" title="No orders yet"
                               body="When someone checks out, their order lands here as pending." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="ad-figure truncate text-[12.5px] font-medium">{{ $order->order_number }}</div>
                                <div class="mt-0.5 truncate text-[11.5px] font-normal text-slate-400">
                                    {{ $order->customer?->name ?? $order->ship_name }} · {{ $order->created_at->diffForHumans(short: true) }}
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2.5">
                                <x-admin.status :status="$order->status" />
                                <span class="ad-figure text-[13px] font-medium">{{ \App\Models\Product::money($order->grand_total) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="ad-card">
            <div class="ad-card-head">
                <div class="ad-card-title">Running out</div>
                <span class="ad-badge ad-badge-warn">≤ {{ \App\Http\Controllers\Admin\DashboardController::LOW_STOCK }} left</span>
            </div>

            @if($lowStock->isEmpty())
                <x-admin.empty icon="check" title="Everything is stocked" body="No active size is down to its last few." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($lowStock as $variant)
                        <a href="{{ route('admin.products.edit', $variant->product) }}"
                           class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-[12.5px] font-normal">{{ $variant->product->name }}</div>
                                <div class="mt-0.5 text-[11.5px] font-normal text-slate-400">{{ $variant->label ?: 'One size' }}</div>
                            </div>
                            <span class="ad-badge shrink-0 {{ $variant->stock === 0 ? 'ad-badge-bad' : 'ad-badge-warn' }}">
                                <span class="ad-figure">{{ $variant->stock }}</span> left
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="ad-card">
            <div class="ad-card-head">
                <div class="ad-card-title">Unread enquiries</div>
                <a href="{{ route('admin.messages.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">Inbox</a>
            </div>

            @if($unreadMessages->isEmpty())
                <x-admin.empty icon="check" title="Inbox clear" body="Every contact-form message has been read." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($unreadMessages as $message)
                        <a href="{{ route('admin.messages.show', $message) }}" class="block px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="truncate text-[12.5px] font-medium">{{ $message->name }}</span>
                                <span class="shrink-0 text-[11px] font-normal text-slate-400">{{ $message->created_at->diffForHumans(short: true) }}</span>
                            </div>
                            <p class="mt-0.5 line-clamp-1 text-[11.5px] font-normal text-slate-500">{{ $message->subject ?: $message->message }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
