@extends('layouts.admin')

@section('title', 'Discount codes')
@section('heading', 'Discount codes')
@section('subheading', 'Codes are matched case-insensitively at the bag and re-validated against the live subtotal on every read.')

@section('actions')
    <button type="button" data-modal-open="coupon-new" class="ad-btn-primary">＋ New code</button>
@endsection

@section('content')
    <div class="ad-card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-[200px] flex-1">
                <label for="q" class="ad-label">Search</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Code…" class="ad-input">
            </div>
            <div class="w-[160px]">
                <label for="filter" class="ad-label">Show</label>
                <select id="filter" name="filter" class="ad-input">
                    @foreach(['' => 'All codes', 'active' => 'Active', 'expired' => 'Expired'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="ad-btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'filter']))
                <a href="{{ route('admin.coupons.index') }}" class="ad-btn">Clear</a>
            @endif
        </form>

        @if($coupons->isEmpty())
            <x-admin.empty icon="％" title="No codes yet"
                           body="Create a percentage off, a fixed amount, or a free-shipping code — with optional minimum spend, usage cap and expiry.">
                <button type="button" data-modal-open="coupon-new" class="ad-btn-primary">＋ New code</button>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Code</th><th>Discount</th><th>Conditions</th><th>Window</th>
                            <th class="text-right">Used</th><th>Status</th><th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            @php
                                $expired = $coupon->expires_at && $coupon->expires_at->isPast();
                                $spent = $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit;
                            @endphp
                            <tr>
                                <td><span class="ad-figure font-semibold tracking-wide">{{ $coupon->code }}</span></td>
                                <td class="whitespace-nowrap">
                                    <span class="font-medium">
                                        {{ $coupon->type === 'percent' ? rtrim(rtrim(number_format($coupon->value, 2), '0'), '.').'%' : \App\Models\Product::money($coupon->value) }} off
                                    </span>
                                    @if($coupon->free_shipping)<span class="ad-badge ad-badge-good ml-1.5">+ free ship</span>@endif
                                </td>
                                <td class="text-[12.5px] font-normal text-slate-600">
                                    {{ $coupon->min_subtotal ? 'Min '.\App\Models\Product::money($coupon->min_subtotal) : 'No minimum' }}
                                    @if($coupon->usage_limit) · cap {{ $coupon->usage_limit }} @endif
                                </td>
                                <td class="text-[12.5px] font-normal whitespace-nowrap text-slate-600">
                                    {{ $coupon->starts_at?->format('j M') ?? 'Now' }} –
                                    <span class="{{ $expired ? 'text-rose-600' : '' }}">{{ $coupon->expires_at?->format('j M Y') ?? 'open' }}</span>
                                </td>
                                <td class="ad-figure text-right">
                                    {{ $coupon->used_count }}@if($coupon->usage_limit)<span class="font-normal text-slate-400"> / {{ $coupon->usage_limit }}</span>@endif
                                </td>
                                <td>
                                    @if(! $coupon->is_active)
                                        <span class="ad-badge ad-badge-neutral">Off</span>
                                    @elseif($expired)
                                        <span class="ad-badge ad-badge-bad">Expired</span>
                                    @elseif($spent)
                                        <span class="ad-badge ad-badge-warn">Used up</span>
                                    @else
                                        <span class="ad-badge ad-badge-good">Active</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" data-modal-open="coupon-{{ $coupon->id }}" class="ad-btn ad-btn-sm">Edit</button>
                                        <button type="button" data-modal-open="delete-coupon-{{ $coupon->id }}" class="ad-btn ad-btn-sm text-rose-600 hover:border-rose-600" title="Delete">✕</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['paginator' => $coupons])
        @endif
    </div>
@endsection

@section('modals')
    {{-- Create --}}
    <x-admin.modal id="coupon-new" title="New discount code" width="max-w-[560px]"
                   :autoopen="$errors->any() && ! old('_coupon_id')">
        @include('admin.coupons.fields', ['coupon' => new \App\Models\Coupon(['type' => 'percent', 'is_active' => true, 'value' => 10]), 'action' => route('admin.coupons.store'), 'method' => 'POST', 'submit' => 'Create code'])
    </x-admin.modal>

    @foreach($coupons as $coupon)
        {{-- Edit --}}
        <x-admin.modal :id="'coupon-'.$coupon->id" :title="'Edit '.$coupon->code" width="max-w-[560px]"
                       :autoopen="$errors->any() && old('_coupon_id') == $coupon->id">
            @include('admin.coupons.fields', ['coupon' => $coupon, 'action' => route('admin.coupons.update', $coupon), 'method' => 'PUT', 'submit' => 'Save code'])
        </x-admin.modal>

        <x-admin.confirm :id="'delete-coupon-'.$coupon->id"
                         :action="route('admin.coupons.destroy', $coupon)"
                         :title="'Delete '.$coupon->code.'?'"
                         confirm="Delete code"
                         body="Orders that already used it keep their discount — the code simply stops being offered." />
    @endforeach
@endsection
