@extends('layouts.admin')

@section('title', 'Customers')
@section('heading', 'Customers')
@section('subheading', number_format($customers->total()).' '.Str::plural('record', $customers->total()).'. Customers never sign in — these are CRM records matched on email at checkout.')

@section('content')
    <div class="ad-card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-[200px] flex-1">
                <label for="q" class="ad-label">Search</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Name, email or phone…" class="ad-input">
            </div>

            <div class="w-[170px]">
                <label for="filter" class="ad-label">Show</label>
                <select id="filter" name="filter" class="ad-input">
                    @foreach(['' => 'Everyone', 'repeat' => 'Repeat buyers', 'subscribed' => 'Opted into email', 'never' => 'Never ordered'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-[150px]">
                <label for="sort" class="ad-label">Sort by</label>
                <select id="sort" name="sort" class="ad-input">
                    <option value="" @selected(request('sort') !== 'value')>Newest</option>
                    <option value="value" @selected(request('sort') === 'value')>Lifetime value</option>
                </select>
            </div>

            <button type="submit" class="ad-btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'filter', 'sort']))
                <a href="{{ route('admin.customers.index') }}" class="ad-btn">Clear</a>
            @endif
        </form>

        @if($customers->isEmpty())
            <x-admin.empty icon="◍" title="No customers match"
                           body="A record is created the first time someone checks out with a given email address." />
        @else
            <div class="overflow-x-auto">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th class="text-right">Orders</th>
                            <th class="text-right">Lifetime</th>
                            <th>Last order</th>
                            <th>Email list</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center gap-2.5 group">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[12px] font-medium text-slate-900">
                                            {{ strtoupper(mb_substr($customer->name ?: $customer->email, 0, 1)) }}
                                        </span>
                                        <span class="max-w-[180px] truncate font-medium group-hover:text-slate-900">{{ $customer->name ?: '—' }}</span>
                                    </a>
                                </td>
                                <td class="max-w-[220px] truncate font-normal text-slate-600">{{ $customer->email }}</td>
                                <td class="ad-figure text-right">{{ $customer->orders_count }}</td>
                                <td class="ad-figure text-right font-medium">{{ \App\Models\Product::money($customer->lifetime_value ?? 0) }}</td>
                                <td class="font-normal whitespace-nowrap text-slate-400">
                                    {{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->diffForHumans(short: true) : 'Never' }}
                                </td>
                                <td>
                                    <span class="ad-badge {{ $customer->marketing_opt_in ? 'ad-badge-good' : 'ad-badge-neutral' }}">
                                        {{ $customer->marketing_opt_in ? 'Subscribed' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="ad-btn ad-btn-sm">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['paginator' => $customers])
        @endif
    </div>
@endsection
