@extends('layouts.admin')

@section('title', $customer->name ?: $customer->email)
@section('heading', $customer->name ?: $customer->email)
@section('subheading', 'On the books since '.$customer->created_at->format('j F Y'))

@section('breadcrumb')
    <a href="{{ route('admin.customers.index') }}" class="hover:text-slate-900">Customers</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $customer->name ?: $customer->email }}</span>
@endsection

@section('actions')
    <a href="mailto:{{ $customer->email }}" class="ad-btn">Email</a>
    <button type="button" data-modal-open="edit-customer" class="ad-btn-primary">Edit details</button>
    <button type="button" data-modal-open="delete-customer" class="ad-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
@endsection

@section('content')
    @php
        $revenue = $customer->orders->filter(fn ($o) => $o->status->countsAsRevenue());
        $lifetime = (float) $revenue->sum('grand_total');
    @endphp

    {{-- Headline figures --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            ['Orders', number_format($customer->orders->count()), 'Including cancelled'],
            ['Lifetime value', \App\Models\Product::money($lifetime), 'Excludes cancelled and refunded'],
            ['Average basket', \App\Models\Product::money($revenue->count() ? $lifetime / $revenue->count() : 0), 'Across counted orders'],
            ['Last order', $customer->orders->first()?->created_at->diffForHumans(short: true) ?? 'Never', $customer->orders->first()?->created_at->format('j M Y') ?? 'No orders placed'],
        ] as [$label, $value, $hint])
            <div class="ad-card p-5">
                <div class="ad-eyebrow">{{ $label }}</div>
                <div class="ad-figure mt-2 text-[22px] leading-none font-medium">{{ $value }}</div>
                <p class="mt-2 text-[11.5px] leading-relaxed font-normal text-slate-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">

        {{-- Order history --}}
        <div class="ad-card">
            <div class="ad-card-head"><div class="ad-card-title">Order history</div></div>

            @if($customer->orders->isEmpty())
                <x-admin.empty icon="❏" title="No orders yet"
                               body="This record exists because the email was entered at checkout or added here by hand." />
            @else
                <div class="overflow-x-auto">
                    <table class="ad-table">
                        <thead>
                            <tr><th>Order</th><th>Placed</th><th class="text-right">Items</th><th>Status</th><th class="text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($customer->orders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="ad-figure font-medium hover:text-slate-900">{{ $order->order_number }}</a></td>
                                    <td class="font-normal whitespace-nowrap text-slate-600">{{ $order->created_at->format('j M Y') }}</td>
                                    <td class="ad-figure text-right font-normal text-slate-600">{{ $order->items_count }}</td>
                                    <td><x-admin.status :status="$order->status" /></td>
                                    <td class="ad-figure text-right font-medium">{{ \App\Models\Product::money($order->grand_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Details + notes --}}
        <div class="flex flex-col gap-5">
            <div class="ad-card">
                <div class="ad-card-head"><div class="ad-card-title">Details</div></div>
                <div class="flex flex-col gap-3.5 px-5 py-5 text-[13px]">
                    <div class="flex justify-between gap-3">
                        <span class="font-normal text-slate-400">Email</span>
                        <a href="mailto:{{ $customer->email }}" class="truncate font-normal hover:text-slate-900">{{ $customer->email }}</a>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="font-normal text-slate-400">Phone</span>
                        <span class="font-normal">{{ $customer->phone ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-normal text-slate-400">Email list</span>
                        <span class="ad-badge {{ $customer->marketing_opt_in ? 'ad-badge-good' : 'ad-badge-neutral' }}">
                            {{ $customer->marketing_opt_in ? 'Subscribed' : 'Not subscribed' }}
                        </span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="font-normal text-slate-400">Added</span>
                        <span class="font-normal">{{ $customer->created_at->format('j M Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="ad-card">
                <div class="ad-card-head"><div class="ad-card-title">Notes</div></div>
                <div class="px-5 py-5">
                    @if($customer->notes)
                        <p class="text-[13px] leading-relaxed font-normal whitespace-pre-line text-slate-600">{{ $customer->notes }}</p>
                    @else
                        <p class="text-[13px] font-normal text-slate-400">
                            Nothing noted. Sizing, preferences, a conversation worth remembering — it goes here.
                        </p>
                    @endif
                    <button type="button" data-modal-open="edit-customer" class="ad-btn ad-btn-sm mt-4">Edit notes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <x-admin.modal id="edit-customer" title="Edit customer" subtitle="Changing the email re-points future checkouts at this record.">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf @method('PUT')

            <div class="flex flex-col gap-4 px-6 py-5">
                <x-admin.field name="name" label="Name" :value="$customer->name" required />
                <x-admin.field name="email" label="Email" type="email" :value="$customer->email" required />
                <x-admin.field name="phone" label="Phone" :value="$customer->phone" />
                <x-admin.field name="notes" label="Notes" type="textarea" :rows="4" :value="$customer->notes"
                               placeholder="Sizing, preferences, anything worth remembering…" />
                <x-admin.toggle name="marketing_opt_in" label="Subscribed to email" :checked="$customer->marketing_opt_in"
                                hint="Only tick this if they actually asked for it." />
            </div>

            <div class="flex justify-end gap-2.5 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button type="button" data-modal-close class="ad-btn">Cancel</button>
                <button type="submit" class="ad-btn-primary">Save customer</button>
            </div>
        </form>
    </x-admin.modal>

    <x-admin.confirm id="delete-customer"
                     :action="route('admin.customers.destroy', $customer)"
                     :title="'Delete '.($customer->name ?: $customer->email).'?'"
                     confirm="Delete customer"
                     body="Their orders are kept — the sales history stays intact and simply stops pointing at a customer record. This cannot be undone." />
@endsection
