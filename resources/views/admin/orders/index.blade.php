@extends('layouts.admin')

@section('title', 'Orders')
@section('heading', 'Orders')
@section('subheading', number_format($orders->total()).' '.Str::plural('order', $orders->total()).' · '.\App\Models\Product::money($revenue).' in revenue from this selection')

@section('content')
    <div class="ad-card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-[200px] flex-1">
                <label for="q" class="ad-label">Search</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Order number, email or name…" class="ad-input">
            </div>

            <div class="w-[160px]">
                <label for="status" class="ad-label">Status</label>
                <select id="status" name="status" class="ad-input">
                    <option value="">Any status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-[150px]">
                <label for="range" class="ad-label">Placed</label>
                <select id="range" name="range" class="ad-input">
                    @foreach(['' => 'All time', 'today' => 'Today', 'week' => 'Past week', 'month' => 'Past month'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('range') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="ad-btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'status', 'range']))
                <a href="{{ route('admin.orders.index') }}" class="ad-btn">Clear</a>
            @endif
        </form>

        @if($orders->isEmpty())
            <x-admin.empty icon="❏" title="No orders match"
                           body="Orders arrive here the moment someone completes checkout. Nothing is charged — they land as pending for you to work through." />
        @else
            <div class="overflow-x-auto">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Placed</th>
                            <th class="text-right">Items</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="ad-figure font-medium hover:text-slate-900">{{ $order->order_number }}</a></td>
                                <td>
                                    <div class="max-w-[220px]">
                                        <div class="truncate font-normal">{{ $order->customer?->name ?? $order->ship_name }}</div>
                                        <div class="truncate text-[11.5px] font-normal text-slate-400">{{ $order->email }}</div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-normal text-slate-600">{{ $order->created_at->format('j M Y') }}</div>
                                    <div class="text-[11.5px] font-normal text-slate-400">{{ $order->created_at->format('H:i') }}</div>
                                </td>
                                <td class="ad-figure text-right font-normal text-slate-600">{{ $order->items_count }}</td>
                                <td>
                                    <button type="button" data-modal-open="status-{{ $order->id }}" class="transition-opacity hover:opacity-70" title="Change status">
                                        <x-admin.status :status="$order->status" />
                                    </button>
                                </td>
                                <td class="ad-figure text-right font-medium whitespace-nowrap">{{ \App\Models\Product::money($order->grand_total) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="ad-btn ad-btn-sm">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['paginator' => $orders])
        @endif
    </div>
@endsection

@section('modals')
    {{-- Change status without leaving the list — the most common thing anyone
         does on this screen. --}}
    @foreach($orders as $order)
        <x-admin.modal :id="'status-'.$order->id"
                       :title="'Order '.$order->order_number"
                       :subtitle="'Placed '.$order->created_at->format('j M Y').' · '.\App\Models\Product::money($order->grand_total)">
            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf @method('PATCH')

                <div class="px-6 py-5">
                    <span class="ad-label">Move this order to</span>
                    <div class="mt-1 grid grid-cols-2 gap-2">
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
    @endforeach
@endsection
