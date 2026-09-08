@extends('layouts.admin')

@section('title', 'Analytics')
@section('heading', 'Analytics')
@section('subheading', 'How the shop is trading. '.$range->label().'.')

@section('actions')
    <a href="{{ route('admin.orders.index') }}" class="bo-btn">Orders</a>
    <a href="{{ route('admin.dashboard') }}" class="bo-btn">Dashboard</a>
@endsection

@section('content')
    @php
        $money = fn ($v) => \App\Models\Product::money($v);
        $keep = $range->toQuery();
    @endphp

    {{-- Window ---------------------------------------------------------- --}}
    <div class="bo-card">
        <form method="GET" class="flex flex-wrap items-end gap-3 px-5 py-4">
            <div>
                <label for="range" class="bo-label">Period</label>
                <select id="range" name="range" class="bo-input w-auto">
                    @foreach(\App\Support\DateRange::PRESETS as $value => $label)
                        <option value="{{ $value }}" @selected($range->preset() === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="from" class="bo-label">From</label>
                <input type="date" id="from" name="from" class="bo-input w-auto"
                       value="{{ request('from', $range->isAllTime() ? '' : $range->start()->toDateString()) }}">
            </div>

            <div>
                <label for="to" class="bo-label">To</label>
                <input type="date" id="to" name="to" class="bo-input w-auto"
                       value="{{ request('to', $range->end()->toDateString()) }}">
            </div>

            <button type="submit" class="bo-btn-primary">Apply</button>

            @if(request()->hasAny(['range', 'from', 'to', 'sort']))
                <a href="{{ route('admin.analytics') }}" class="bo-btn">Clear</a>
            @endif

            <p class="ml-auto max-w-[34ch] text-[11.5px] leading-relaxed font-normal text-slate-400">
                Dates apply when the period is set to a custom range.
            </p>
        </form>

        <div class="flex flex-wrap gap-1.5 border-t border-slate-100 px-5 py-3">
            @foreach(['traffic' => 'Traffic', 'sales' => 'Sales', 'engagement' => 'Engagement', 'customers' => 'Customers', 'inventory' => 'Inventory'] as $anchor => $label)
                <a href="#{{ $anchor }}" class="bo-btn bo-btn-sm">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Headline -------------------------------------------------------- --}}
    <div class="mt-5 grid grid-cols-2 gap-4 xl:grid-cols-6">
        @foreach($kpis as $kpi)
            <div class="bo-card p-5">
                <div class="bo-eyebrow">{{ $kpi['label'] }}</div>
                <div class="mt-2.5 flex flex-wrap items-baseline gap-2">
                    <span class="bo-figure text-[22px] leading-none font-medium">{{ $kpi['value'] }}</span>
                    @if($kpi['delta'] !== null)
                        <span class="bo-badge {{ $kpi['delta'] >= 0 ? 'bo-badge-good' : 'bo-badge-bad' }}">
                            {{ $kpi['delta'] >= 0 ? '↑' : '↓' }} {{ abs($kpi['delta']) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-2.5 text-[11.5px] leading-relaxed font-normal text-slate-400">{{ $kpi['hint'] }}</p>
            </div>
        @endforeach
    </div>

    @if($range->previous())
        <p class="mt-2.5 text-[12px] font-normal text-slate-400">
            Changes compare against
            {{ $range->previous()->start()->format('j M Y') }} – {{ $range->previous()->end()->format('j M Y') }}.
        </p>
    @endif

    {{-- ================= TRAFFIC ======================================= --}}
    <h2 id="traffic" class="mt-9 scroll-mt-24 text-[15px] font-semibold tracking-[-0.01em] text-slate-800">Traffic</h2>

    <div class="mt-3.5 grid grid-cols-2 gap-4 xl:grid-cols-5">
        @foreach($traffic as $stat)
            <div class="bo-card p-5">
                <div class="bo-eyebrow">{{ $stat['label'] }}</div>
                <div class="mt-2.5 flex flex-wrap items-baseline gap-2">
                    <span class="bo-figure text-[22px] leading-none font-medium">{{ $stat['value'] }}</span>
                    @if($stat['delta'] !== null)
                        <span class="bo-badge {{ $stat['delta'] >= 0 ? 'bo-badge-good' : 'bo-badge-bad' }}">
                            {{ $stat['delta'] >= 0 ? '↑' : '↓' }} {{ abs($stat['delta']) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-2.5 text-[11.5px] leading-relaxed font-normal text-slate-400">{{ $stat['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="bo-card xl:col-span-2">
            <div class="bo-card-head">
                <div>
                    <div class="bo-card-title">Page views over time</div>
                    <p class="mt-0.5 text-[12px] font-normal text-slate-400">
                        <span class="bo-figure text-slate-800">{{ number_format($trafficSeries->sum('value')) }}</span> pages opened
                    </p>
                </div>
                <span class="bo-badge bo-badge-neutral">Peak {{ number_format($trafficSeries->max('value') ?? 0) }}</span>
            </div>

            @if($trafficSeries->sum('value') <= 0)
                <x-admin.empty icon="analytics" title="No traffic recorded in this period"
                               body="Page views are logged from the moment the tracking went live — earlier periods will be empty." />
            @else
                <x-admin.bar-chart :series="$trafficSeries" class="px-5 py-6" />
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Where visits come from</div>
                <span class="text-[12px] font-normal text-slate-400">Visits</span>
            </div>

            @if($sources->isEmpty())
                <x-admin.empty icon="analytics" title="Nothing to attribute" body="No visits were recorded in this period." />
            @else
                @php $sourcePeak = $sources->max('visits'); @endphp
                <div class="flex flex-col gap-3.5 px-5 py-5">
                    @foreach($sources->take(8) as $i => $row)
                        <x-admin.bar-row :label="$row['label']"
                                         :value="$row['visits']"
                                         :max="$sourcePeak"
                                         :delay="$i * 35" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Most visited pages</div>
            </div>

            @if($topPages->isEmpty())
                <x-admin.empty icon="analytics" title="No pages recorded" body="Nothing was opened in this period." />
            @else
                <div class="overflow-x-auto">
                    <table class="bo-table">
                        <thead>
                            <tr><th>Page</th><th class="text-right">Views</th><th class="text-right">Visits</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topPages as $page)
                                <tr>
                                    <td class="max-w-[220px] truncate">
                                        <a href="{{ url($page['path']) }}" target="_blank" rel="noopener"
                                           class="bo-figure text-[12.5px] hover:text-slate-900">{{ $page['path'] }}</a>
                                    </td>
                                    <td class="bo-figure text-right font-medium">{{ number_format($page['views']) }}</td>
                                    <td class="bo-figure text-right font-normal text-slate-600">{{ number_format($page['visits']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Custom events</div>
                <span class="text-[12px] font-normal text-slate-400">Times · people</span>
            </div>

            <div class="flex flex-col divide-y divide-slate-100">
                @foreach($customEvents as $event)
                    <div class="flex items-baseline justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <div class="truncate text-[13px] font-normal text-slate-700">{{ $event['type']->label() }}</div>
                            <div class="mt-0.5 truncate text-[11px] font-normal text-slate-400">{{ $event['type']->hint() }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="bo-figure text-[14px] font-medium">{{ number_format($event['total']) }}</span>
                            <span class="bo-figure ml-1.5 text-[11.5px] font-normal text-slate-400">{{ number_format($event['visitors']) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">What people search for</div>
            </div>

            @if($searches->isEmpty())
                <x-admin.empty icon="search" title="No searches yet" body="Terms typed into the header search appear here." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($searches as $search)
                        <a href="{{ route('listing', ['q' => $search['term']]) }}" target="_blank" rel="noopener"
                           class="flex items-baseline justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-[13px] font-normal text-slate-700">{{ $search['term'] }}</div>
                                @if($search['results'] === 0)
                                    <div class="mt-0.5 text-[11px] font-medium text-rose-600">Found nothing</div>
                                @else
                                    <div class="mt-0.5 text-[11px] font-normal text-slate-400">
                                        {{ $search['results'] }} {{ \Illuminate\Support\Str::plural('result', $search['results']) }}
                                    </div>
                                @endif
                            </div>
                            <span class="bo-figure shrink-0 text-[13px] font-medium">{{ number_format($search['searches']) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ================= SALES ========================================= --}}
    <h2 id="sales" class="mt-9 scroll-mt-24 text-[15px] font-semibold tracking-[-0.01em] text-slate-800">Sales</h2>

    <div class="mt-3.5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="bo-card xl:col-span-2">
            <div class="bo-card-head">
                <div>
                    <div class="bo-card-title">Revenue</div>
                    <p class="mt-0.5 text-[12px] font-normal text-slate-400">
                        <span class="bo-figure text-slate-800">{{ $money($revenueSeries->sum('value')) }}</span>
                        across {{ $revenueSeries->count() }} {{ \Illuminate\Support\Str::plural($range->granularity(), $revenueSeries->count()) }}
                    </p>
                </div>
                <span class="bo-badge bo-badge-neutral">Peak {{ $money($revenueSeries->max('value') ?? 0) }}</span>
            </div>

            @if($revenueSeries->sum('value') <= 0)
                <x-admin.empty icon="orders" title="Nothing taken in this period"
                               body="Widen the window, or check that orders have not all been cancelled." />
            @else
                <x-admin.bar-chart :series="$revenueSeries" format="money" class="px-5 py-6" />
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Where it came from</div>
            </div>
            <div class="flex flex-col divide-y divide-slate-100">
                @foreach($takings as $line)
                    <div class="flex items-baseline justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0">
                            <div class="text-[13px] font-normal text-slate-600">{{ $line['label'] }}</div>
                            <div class="mt-0.5 text-[11px] font-normal text-slate-400">{{ $line['note'] }}</div>
                        </div>
                        <span class="bo-figure shrink-0 text-[14px] font-medium">{{ $line['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">By category</div>
                <span class="text-[12px] font-normal text-slate-400">Revenue</span>
            </div>

            @if($categoryRevenue->isEmpty())
                <x-admin.empty icon="products" title="No sales to split" body="Nothing sold in this period." />
            @else
                @php $categoryPeak = $categoryRevenue->max('revenue'); @endphp
                <div class="flex flex-col gap-3.5 px-5 py-5">
                    @foreach($categoryRevenue as $i => $row)
                        <x-admin.bar-row :label="$row['label']"
                                         :value="$row['revenue']"
                                         :max="$categoryPeak"
                                         :display="$money($row['revenue'])"
                                         :note="number_format($row['units']).' units'"
                                         :delay="$i * 35" />
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Order status</div>
                <a href="{{ route('admin.orders.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">View all</a>
            </div>

            @php $mixTotal = max(array_sum($statusMix), 1); @endphp
            <div class="flex flex-col gap-3.5 px-5 py-5">
                @foreach(\App\Enums\OrderStatus::cases() as $i => $status)
                    <x-admin.bar-row :label="$status->label()"
                                     :value="$statusMix[$status->value] ?? 0"
                                     :max="$mixTotal"
                                     :delay="$i * 35"
                                     :href="route('admin.orders.index', ['status' => $status->value])" />
                @endforeach
            </div>
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Discount codes</div>
            </div>

            @if($coupons->isEmpty())
                <x-admin.empty icon="coupons" title="No codes used" body="Nothing in this period was ordered with a discount code." />
            @else
                {{-- A list rather than a table: this card is a third of a row,
                     and four numeric columns would scroll sideways in it. --}}
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($coupons as $coupon)
                        <div class="flex items-baseline justify-between gap-3 px-5 py-3.5">
                            <div class="min-w-0">
                                <div class="bo-figure truncate text-[13px] font-medium">{{ $coupon->code }}</div>
                                <div class="mt-0.5 text-[11px] font-normal text-slate-400">
                                    {{ number_format($coupon->uses) }} {{ \Illuminate\Support\Str::plural('use', $coupon->uses) }}
                                    · −{{ $money($coupon->discount_given ?? 0) }} given
                                </div>
                            </div>
                            <span class="bo-figure shrink-0 text-[14px] font-medium">{{ $money($coupon->revenue_taken ?? 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ================= ENGAGEMENT ==================================== --}}
    <h2 id="engagement" class="mt-9 scroll-mt-24 text-[15px] font-semibold tracking-[-0.01em] text-slate-800">Product engagement</h2>

    <div class="mt-3.5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Viewed, bagged, bought</div>
            </div>

            @php $funnelPeak = max(collect($funnel)->max('value'), 1); @endphp
            <div class="flex flex-col gap-4 px-5 py-5">
                @foreach($funnel as $i => $step)
                    <x-admin.bar-row :label="$step['label']"
                                     :value="$step['value']"
                                     :max="$funnelPeak"
                                     :note="$step['note']"
                                     :delay="$i * 60" />
                @endforeach
            </div>

            <p class="border-t border-slate-100 px-5 py-3 text-[11.5px] leading-relaxed font-normal text-slate-400">
                Three counts of the same period, not one shopper followed through: views are
                deduplicated per visitor for 30 minutes, and orders carry no visitor.
            </p>
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Looked at, never bought</div>
            </div>

            @if($unsold->isEmpty())
                <x-admin.empty icon="check" title="Nothing stranded" body="Every piece that drew views in this period also sold." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($unsold as $product)
                        <a href="{{ route('admin.products.edit', $product) }}"
                           class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-[13px] font-normal">{{ $product->name }}</div>
                                <div class="mt-0.5 text-[11.5px] font-normal text-slate-400">{{ $product->category?->name }}</div>
                            </div>
                            <span class="bo-badge bo-badge-warn shrink-0">
                                <span class="bo-figure">{{ number_format($product->views_count) }}</span> views
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Most saved</div>
                <span class="bo-badge bo-badge-neutral">All time</span>
            </div>

            @if($mostSaved->isEmpty())
                <x-admin.empty icon="heart" title="Nothing saved yet" body="Favourites appear here once shoppers start hearting pieces." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($mostSaved as $product)
                        <a href="{{ route('admin.products.edit', $product) }}"
                           class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-[13px] font-normal">{{ $product->name }}</div>
                                <div class="mt-0.5 text-[11.5px] font-normal text-slate-400">{{ $product->category?->name }}</div>
                            </div>
                            <span class="bo-figure shrink-0 text-[13px] font-medium">{{ number_format($product->favorites_count) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="bo-card mt-5">
        <div class="bo-card-head">
            <div class="bo-card-title">Every piece</div>
            <span class="text-[12px] font-normal text-slate-400">
                Sold and viewed in the period · favourites all time
            </span>
        </div>

        @if($products->isEmpty())
            <x-admin.empty icon="products" title="No products yet"
                           body="Add your first piece and its engagement will show up here.">
                <a href="{{ route('admin.products.create') }}" class="bo-btn-primary">＋ New product</a>
            </x-admin.empty>
        @else
            @php
                $columns = ['sold' => 'Sold', 'views' => 'Views', 'bag' => 'Added to bag', 'saved' => 'Saved'];
            @endphp
            <div class="overflow-x-auto">
                <table class="bo-table">
                    <thead>
                        <tr>
                            <th>Piece</th>
                            <th class="text-right">Revenue</th>
                            @foreach($columns as $key => $label)
                                <th class="text-right">
                                    <a href="{{ route('admin.analytics', $keep + ['sort' => $key]) }}#products"
                                       class="hover:text-slate-700 {{ $sort === $key ? 'text-slate-800 underline' : '' }}">{{ $label }}</a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="block hover:text-slate-900">
                                        <span class="font-normal">{{ $product->name }}</span>
                                        <span class="mt-0.5 block text-[11.5px] font-normal text-slate-400">{{ $product->category?->name }}</span>
                                    </a>
                                </td>
                                <td class="bo-figure text-right font-medium">{{ $money($product->revenue_sold ?? 0) }}</td>
                                <td class="bo-figure text-right font-medium">{{ (int) $product->units_sold }}</td>
                                <td class="bo-figure text-right font-normal text-slate-600">{{ number_format($product->views_count) }}</td>
                                <td class="bo-figure text-right font-normal text-slate-600">{{ number_format($product->add_to_cart_count) }}</td>
                                <td class="bo-figure text-right font-normal text-slate-600">{{ number_format($product->favorites_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['paginator' => $products])
        @endif
    </div>

    {{-- ================= CUSTOMERS ===================================== --}}
    <h2 id="customers" class="mt-9 scroll-mt-24 text-[15px] font-semibold tracking-[-0.01em] text-slate-800">Customers</h2>

    <div class="mt-3.5 grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach($customers as $stat)
            <div class="bo-card p-5">
                <div class="bo-eyebrow">{{ $stat['label'] }}</div>
                <div class="bo-figure mt-2.5 text-[22px] leading-none font-medium">{{ $stat['value'] }}</div>
                <p class="mt-2.5 text-[11.5px] leading-relaxed font-normal text-slate-400">{{ $stat['note'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="bo-card xl:col-span-2">
            <div class="bo-card-head">
                <div class="bo-card-title">Top spenders</div>
                <a href="{{ route('admin.customers.index') }}" class="text-[12px] font-medium text-slate-900 hover:underline">All customers</a>
            </div>

            @if($topCustomers->isEmpty())
                <x-admin.empty icon="customers" title="Nobody has ordered yet" body="Customers appear here once an order is placed in this period." />
            @else
                <div class="overflow-x-auto">
                    <table class="bo-table">
                        <thead>
                            <tr><th>Customer</th><th>Reach them</th><th class="text-right">Orders</th><th class="text-right">Spend</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="font-normal hover:text-slate-900">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/{{ $customer->phone }}" target="_blank" rel="noopener"
                                           class="bo-figure text-slate-500 hover:text-slate-900">{{ $customer->phone }}</a>
                                    </td>
                                    <td class="bo-figure text-right">{{ number_format($customer->order_count) }}</td>
                                    <td class="bo-figure text-right font-medium">{{ $money($customer->spend ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">New customers</div>
                <span class="bo-figure text-[12px] font-medium text-slate-800">{{ number_format($customerSeries->sum('value')) }}</span>
            </div>

            @if($customerSeries->sum('value') <= 0)
                <x-admin.empty icon="customers" title="Nobody new" body="No first-time customer records were created in this period." />
            @else
                <x-admin.bar-chart :series="$customerSeries" height="150px" class="px-5 py-6" />
            @endif
        </div>
    </div>

    {{-- ================= INVENTORY ===================================== --}}
    <h2 id="inventory" class="mt-9 scroll-mt-24 text-[15px] font-semibold tracking-[-0.01em] text-slate-800">Inventory</h2>

    <div class="mt-3.5 grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach($inventory as $stat)
            <div class="bo-card p-5">
                <div class="bo-eyebrow">{{ $stat['label'] }}</div>
                <div class="bo-figure mt-2.5 text-[22px] leading-none font-medium">{{ $stat['value'] }}</div>
                <p class="mt-2.5 text-[11.5px] leading-relaxed font-normal text-slate-400">{{ $stat['note'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Never sold</div>
                <span class="bo-badge bo-badge-warn">Holding stock</span>
            </div>

            @if($deadStock->isEmpty())
                <x-admin.empty icon="check" title="Everything has sold" body="No active piece with stock is still waiting for its first sale." />
            @else
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($deadStock as $product)
                        <a href="{{ route('admin.products.edit', $product) }}"
                           class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-[13px] font-normal">{{ $product->name }}</div>
                                <div class="mt-0.5 text-[11.5px] font-normal text-slate-400">{{ $product->category?->name }}</div>
                            </div>
                            <span class="bo-badge bo-badge-neutral shrink-0">
                                <span class="bo-figure">{{ (int) $product->stock_total }}</span> in stock
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bo-card">
            <div class="bo-card-head">
                <div class="bo-card-title">Stock by category</div>
                <span class="text-[12px] font-normal text-slate-400">Units on hand</span>
            </div>

            @if($stockByCategory->isEmpty())
                <x-admin.empty icon="products" title="No stock recorded" body="Add sizes with stock against a product and they appear here." />
            @else
                @php $stockPeak = $stockByCategory->max('units'); @endphp
                <div class="flex flex-col gap-3.5 px-5 py-5">
                    @foreach($stockByCategory as $i => $row)
                        <x-admin.bar-row :label="$row['label']"
                                         :value="$row['units']"
                                         :max="$stockPeak"
                                         :delay="$i * 35" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
