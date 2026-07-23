@extends('layouts.admin')

@section('title', 'Products')
@section('heading', 'Products')
@section('subheading', number_format($products->total()).' '.Str::plural('piece', $products->total()).' in the catalogue')

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="ad-btn-primary">＋ New product</a>
@endsection

@section('content')
    <div class="ad-card">
        {{-- Filters. A plain GET form, so every view of this list is a URL you
             can bookmark or send to someone. --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-[200px] flex-1">
                <label for="q" class="ad-label">Search</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Name, description, colour…" class="ad-input">
            </div>

            <div class="w-[190px]">
                <label for="category" class="ad-label">Category</label>
                <select id="category" name="category" class="ad-input">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-[150px]">
                <label for="status" class="ad-label">Status</label>
                <select id="status" name="status" class="ad-input">
                    @foreach(['' => 'Everything', 'active' => 'Live', 'draft' => 'Draft', 'featured' => 'Featured', 'sale' => 'On sale'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="ad-btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'category', 'status']))
                <a href="{{ route('admin.products.index') }}" class="ad-btn">Clear</a>
            @endif
        </form>

        @if($products->isEmpty())
            <x-admin.empty icon="⬚" title="No products match"
                           body="Either the catalogue is empty or the filters above are too narrow.">
                <a href="{{ route('admin.products.create') }}" class="ad-btn-primary">＋ New product</a>
                @if(request()->hasAny(['q', 'category', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="ad-btn">Clear filters</a>
                @endif
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Piece</th>
                            <th>Category</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Stock</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-10 shrink-0 overflow-hidden rounded-md border border-slate-100 bg-slate-100">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="block max-w-[280px] truncate font-medium hover:text-slate-900">{{ $product->name }}</a>
                                            <div class="mt-0.5 flex items-center gap-2 text-[11.5px] font-normal text-slate-400">
                                                <span class="truncate">/{{ $product->slug }}</span>
                                                @if($product->badge_label)
                                                    <span class="rounded-sm bg-slate-100 px-1.5 py-px text-[10px] font-medium tracking-wide text-slate-900">{{ $product->badge_label }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="font-normal whitespace-nowrap text-slate-600">{{ $product->category?->name ?? '—' }}</td>

                                <td class="text-right whitespace-nowrap">
                                    <span class="ad-figure font-medium">{{ $product->price_label }}</span>
                                    @if($product->compare_label)
                                        <span class="ad-figure mt-0.5 block text-[11.5px] font-normal text-slate-400 line-through">{{ $product->compare_label }}</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    @php $stock = (int) $product->stock_total; @endphp
                                    <span class="ad-badge {{ $stock === 0 ? 'ad-badge-bad' : ($stock <= 5 ? 'ad-badge-warn' : 'ad-badge-neutral') }}">
                                        <span class="ad-figure">{{ $stock }}</span>
                                    </span>
                                </td>

                                <td>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="field" value="is_active">
                                            <button type="submit" class="ad-badge {{ $product->is_active ? 'ad-badge-good' : 'ad-badge-neutral' }} transition-opacity hover:opacity-70"
                                                    title="{{ $product->is_active ? 'Hide from the shop' : 'Publish to the shop' }}">
                                                {{ $product->is_active ? 'Live' : 'Draft' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="field" value="is_featured">
                                            <button type="submit" class="ad-badge {{ $product->is_featured ? 'border-slate-900/35 bg-slate-900/10 text-slate-900' : 'ad-badge-neutral opacity-55' }} transition-opacity hover:opacity-100"
                                                    title="{{ $product->is_featured ? 'Remove from the featured rail' : 'Feature on the home page' }}">★</button>
                                        </form>
                                    </div>
                                </td>

                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('product', $product) }}" target="_blank" rel="noopener" class="ad-btn ad-btn-sm" title="View on the shop">↗</a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="ad-btn ad-btn-sm">Edit</a>
                                        <button type="button" data-modal-open="delete-product-{{ $product->id }}" class="ad-btn ad-btn-sm text-rose-600 hover:border-rose-600 hover:text-rose-600" title="Delete">✕</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['paginator' => $products])
        @endif
    </div>
@endsection

@section('modals')
    @foreach($products as $product)
        <x-admin.confirm :id="'delete-product-'.$product->id"
                         :action="route('admin.products.destroy', $product)"
                         :title="'Delete '.$product->name.'?'"
                         confirm="Delete product"
                         body="Its photographs, sizes and colours go with it. Orders already placed keep their own copy of the name and price, so sales history is not affected." />
    @endforeach
@endsection
