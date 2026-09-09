@extends('layouts.admin')

@section('title', 'Offers')
@section('heading', 'Offers')
@section('subheading', 'Automatic, cart-level promotions — no code to type. The bag applies whichever offer (or coupon) saves the shopper the most.')

@section('actions')
    <button type="button" data-modal-open="offer-new" class="bo-btn-primary">＋ New offer</button>
@endsection

@section('content')
    @php
        $showOptions = ['' => 'All offers', 'active' => 'Live', 'expired' => 'Expired'];
        $filters = \App\Support\AdminFilters::active([
            'q' => 'Search',
            'filter' => ['Show', $showOptions],
        ]);
    @endphp

    <div class="bo-card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-[200px] flex-1">
                <label for="q" class="bo-label">Search</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Name…" class="bo-input">
            </div>
            <div class="w-[160px]">
                <label for="filter" class="bo-label">Show</label>
                <select id="filter" name="filter" class="bo-input">
                    @foreach($showOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bo-btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'filter']))
                <a href="{{ route('admin.offers.index') }}" class="bo-btn">Clear</a>
            @endif
        </form>

        @if($offers->isEmpty() && $filters)
            <x-admin.no-results noun="offers" :filters="$filters" :reset="route('admin.offers.index')">
                <button type="button" data-modal-open="offer-new" class="bo-btn-primary">＋ New offer</button>
            </x-admin.no-results>
        @elseif($offers->isEmpty())
            <x-admin.empty icon="offers" title="No offers yet"
                           body="A spend threshold, a category percentage off, or a buy-X-get-Y-free — applied automatically, no code needed.">
                <button type="button" data-modal-open="offer-new" class="bo-btn-primary">＋ New offer</button>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="bo-table">
                    <thead>
                        <tr>
                            <th>Name</th><th>Type</th><th>Rule</th><th>Window</th>
                            <th>Status</th><th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offers as $offer)
                            @php
                                $expired = $offer->expires_at && $offer->expires_at->isPast();
                            @endphp
                            <tr>
                                <td>
                                    <span class="bo-figure font-semibold">{{ $offer->name }}</span>
                                    <div class="text-[12px] font-normal text-slate-500">{{ $offer->display_headline }}</div>
                                </td>
                                <td class="text-[12.5px] font-normal whitespace-nowrap text-slate-600">{{ $offer->type->label() }}</td>
                                <td class="text-[12.5px] font-normal text-slate-600">
                                    @if($offer->type === \App\Enums\OfferType::Spend)
                                        {{ $offer->min_subtotal ? 'Min '.\App\Models\Product::money($offer->min_subtotal) : 'No minimum' }}
                                        @if($offer->free_shipping)<span class="bo-badge bo-badge-good ml-1.5">+ free ship</span>@endif
                                    @elseif($offer->type === \App\Enums\OfferType::CategoryPercent)
                                        {{ $offer->products->isNotEmpty() ? $offer->products->count().' '.Str::plural('product', $offer->products->count()) : ($offer->category->name ?? '—') }}
                                    @else
                                        {{ $offer->products->isNotEmpty() ? $offer->products->count().' '.Str::plural('product', $offer->products->count()) : ($offer->category->name ?? 'Storewide') }}
                                    @endif
                                </td>
                                <td class="text-[12.5px] font-normal whitespace-nowrap text-slate-600">
                                    {{ $offer->starts_at?->format('j M') ?? 'Now' }} –
                                    <span class="{{ $expired ? 'text-rose-600' : '' }}">{{ $offer->expires_at?->format('j M Y') ?? 'open' }}</span>
                                </td>
                                <td>
                                    @if(! $offer->is_active)
                                        <span class="bo-badge bo-badge-neutral">Off</span>
                                    @elseif($expired)
                                        <span class="bo-badge bo-badge-bad">Expired</span>
                                    @else
                                        <span class="bo-badge bo-badge-good">Live</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" data-modal-open="offer-{{ $offer->id }}" class="bo-btn bo-btn-sm">Edit</button>
                                        <button type="button" data-modal-open="delete-offer-{{ $offer->id }}" class="bo-btn bo-btn-sm text-rose-600 hover:border-rose-600" title="Delete">✕</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['paginator' => $offers])
        @endif
    </div>
@endsection

@section('modals')
    {{-- Create --}}
    <x-admin.modal id="offer-new" title="New offer" width="max-w-[600px]"
                   :autoopen="$errors->any() && ! old('_offer_id')">
        @include('admin.offers.fields', [
            'offer' => new \App\Models\Offer(['type' => 'spend', 'discount_type' => 'percent', 'is_active' => true, 'value' => 10]),
            'action' => route('admin.offers.store'), 'method' => 'POST', 'submit' => 'Create offer',
            'categoryOptions' => $categoryOptions, 'productOptions' => $productOptions,
        ])
    </x-admin.modal>

    @foreach($offers as $offer)
        {{-- Edit --}}
        <x-admin.modal :id="'offer-'.$offer->id" :title="'Edit '.$offer->name" width="max-w-[600px]"
                       :autoopen="$errors->any() && old('_offer_id') == $offer->id">
            @include('admin.offers.fields', [
                'offer' => $offer, 'action' => route('admin.offers.update', $offer), 'method' => 'PUT', 'submit' => 'Save offer',
                'categoryOptions' => $categoryOptions, 'productOptions' => $productOptions,
            ])
        </x-admin.modal>

        <x-admin.confirm :id="'delete-offer-'.$offer->id"
                         :action="route('admin.offers.destroy', $offer)"
                         :title="'Delete '.$offer->name.'?'"
                         confirm="Delete offer"
                         body="Orders that already used it keep their discount — the offer simply stops applying." />
    @endforeach
@endsection
