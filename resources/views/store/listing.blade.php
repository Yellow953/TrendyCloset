@extends('layouts.storefront')

@php
    $editLabels = ['new' => 'New In', 'sale' => 'Sale', 'featured' => "Leila's Picks"];
    $heading = $category?->name ?? ($editLabels[$edit] ?? 'Shop All');
    // Every filter link keeps the other filters and resets to page one.
    $filterUrl = fn (array $params) => request()->fullUrlWithQuery($params + ['page' => 1]);
    $clearUrl = fn (array $keys) => request()->fullUrlWithoutQuery([...$keys, 'page']);
    $activeFilters = collect(['size' => $filters['size'], 'color' => $filters['color']])->filter();
@endphp

@section('title', $heading.' — Trendy Closet')

@section('content')
    <div class="bg-cream px-5 py-9 text-center md:px-10">
        <h1 class="text-[32px] font-normal md:text-[38px]">{{ $heading }}</h1>
        <div class="mt-2 text-[13px] font-light text-muted">
            <a href="{{ route('home') }}" class="hover:text-blush">Home</a>
            @if($category?->parent)
                / <a href="{{ route('listing', $category->parent) }}" class="hover:text-blush">{{ $category->parent->name }}</a>
            @endif
            / <span class="text-ink">{{ $heading }}</span>
        </div>
    </div>

    <div class="flex flex-col gap-9 px-5 py-10 md:px-10 lg:flex-row lg:gap-12">
        {{-- Filter rail — each group is a collapsible bordered panel --}}
        <aside class="flex w-full flex-col gap-5 lg:w-[310px] lg:flex-none">
            <details open class="group/f border border-line">
                <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4">
                    <span class="text-[16px] font-medium">Shop by Category</span>
                    <span class="text-[18px] leading-none text-muted"><span class="group-open/f:hidden">+</span><span class="hidden group-open/f:inline">−</span></span>
                </summary>
                <div class="border-t border-line px-6 pb-5 pt-4">
                    <a href="{{ route('listing') }}" class="flex justify-between py-1.5 text-[14.5px] transition-colors hover:text-blush {{ $category ? 'font-light text-muted-3' : 'font-medium text-blush' }}">
                        {{-- Root counts only; counts() also holds children, which would double up --}}
                        <span>All products</span><span class="text-faint">({{ $navTree->sum(fn ($r) => $catalog->countFor($r)) }})</span>
                    </a>
                    @foreach($navTree as $root)
                        <a href="{{ route('listing', $root) }}" class="flex justify-between py-1.5 text-[14.5px] transition-colors hover:text-blush {{ $category?->id === $root->id ? 'font-medium text-blush' : 'font-light text-muted-3' }}">
                            <span>{{ $root->name }}</span><span class="text-faint">({{ $catalog->countFor($root) }})</span>
                        </a>
                        @if($root->children->isNotEmpty() && in_array($root->id, [$category?->id, $category?->parent_id], true))
                            <div class="my-1 ml-3 border-l border-line pl-3.5">
                                @foreach($root->children as $child)
                                    <a href="{{ route('listing', $child) }}" class="flex justify-between py-1 text-[14px] transition-colors hover:text-blush {{ $category?->id === $child->id ? 'font-medium text-blush' : 'font-light text-muted-3' }}">
                                        <span>{{ $child->name }}</span><span class="text-faint">({{ $catalog->countFor($child) }})</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </details>

            <details open class="group/f border border-line">
                <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4">
                    <span class="text-[16px] font-medium">Highlight</span>
                    <span class="text-[18px] leading-none text-muted"><span class="group-open/f:hidden">+</span><span class="hidden group-open/f:inline">−</span></span>
                </summary>
                <div class="flex flex-col border-t border-line px-6 pb-5 pt-4">
                    @php($editTabs = ['' => 'All Products', 'featured' => 'Best Sellers', 'new' => 'New Arrivals', 'sale' => 'Sale'])
                    @foreach($editTabs as $key => $label)
                        <a href="{{ $key === '' ? $clearUrl(['edit']) : $filterUrl(['edit' => $key]) }}"
                           class="py-1.5 text-[14.5px] transition-colors hover:text-blush {{ (string) $edit === $key ? 'font-medium text-blush' : 'font-light text-muted-3' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </details>

            @if($sizes->isNotEmpty())
                <details open class="group/f border border-line">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4">
                        <span class="text-[16px] font-medium">Size</span>
                        <span class="text-[18px] leading-none text-muted"><span class="group-open/f:hidden">+</span><span class="hidden group-open/f:inline">−</span></span>
                    </summary>
                    <div class="border-t border-line px-6 pb-5 pt-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach($sizes as $s)
                                <a href="{{ $filters['size'] === $s ? $clearUrl(['size']) : $filterUrl(['size' => $s]) }}"
                                   class="min-w-[52px] border px-2 py-2 text-center text-[13.5px] transition-colors {{ $filters['size'] === $s ? 'border-blush bg-blush text-white' : 'border-line-2 hover:border-blush hover:text-blush' }}">{{ $s }}</a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            @if($colors->isNotEmpty())
                <details open class="group/f border border-line">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4">
                        <span class="text-[16px] font-medium">Colour</span>
                        <span class="text-[18px] leading-none text-muted"><span class="group-open/f:hidden">+</span><span class="hidden group-open/f:inline">−</span></span>
                    </summary>
                    <div class="border-t border-line px-6 pb-5 pt-4">
                        <div class="flex flex-wrap gap-3">
                            @foreach($colors as $c)
                                <a href="{{ $filters['color'] === $c ? $clearUrl(['color']) : $filterUrl(['color' => $c]) }}"
                                   title="{{ $c }}" aria-label="{{ $c }}"
                                   class="h-7 w-7 rounded-full {{ \App\Support\Swatch::needsOutline($c) ? 'border border-line-2' : '' }} {{ $filters['color'] === $c ? 'outline-2 outline-offset-2 outline-blush' : '' }}"
                                   style="background-color: {{ \App\Support\Swatch::hex($c) }}"></a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            <details open class="group/f border border-line">
                <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4">
                    <span class="text-[16px] font-medium">Price</span>
                    <span class="text-[18px] leading-none text-muted"><span class="group-open/f:hidden">+</span><span class="hidden group-open/f:inline">−</span></span>
                </summary>
                <div class="border-t border-line px-6 pb-5 pt-4">
                    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3">
                        @foreach(request()->except(['min', 'max', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <div class="flex items-center gap-2">
                            <input type="number" name="min" min="0" step="1" value="{{ $filters['min'] }}" placeholder="{{ (int) $priceFloor }}" aria-label="Minimum price" class="w-full border border-line-2 px-3 py-2.5 text-[13.5px] font-light outline-none focus:border-blush">
                            <span class="text-muted">—</span>
                            <input type="number" name="max" min="0" step="1" value="{{ $filters['max'] }}" placeholder="{{ (int) ceil($priceCeiling) }}" aria-label="Maximum price" class="w-full border border-line-2 px-3 py-2.5 text-[13.5px] font-light outline-none focus:border-blush">
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="border border-ink px-5 py-2 text-[13px] font-medium transition-colors hover:bg-ink hover:text-white">Apply</button>
                            @if($filters['min'] || $filters['max'])
                                <a href="{{ $clearUrl(['min', 'max']) }}" class="text-[12.5px] font-light text-blush underline underline-offset-2">Reset</a>
                            @endif
                        </div>
                        <div class="text-[13px] font-light text-muted-2">{{ \App\Models\Product::money($priceFloor) }} — {{ \App\Models\Product::money($priceCeiling) }} in this edit</div>
                    </form>
                </div>
            </details>

            <a href="{{ route('listing', ['edit' => 'sale']) }}" class="relative block h-[320px] overflow-hidden bg-tan">
                <img src="{{ $sideBanner['img'] }}" alt="Shop the sale" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                <div class="pointer-events-none absolute inset-0 flex flex-col justify-end gap-1.5 bg-gradient-to-t from-white/85 to-transparent p-6">
                    <div class="text-[22px] font-normal leading-[1.2]">Sale is<br>live now</div>
                    <div class="text-[13px] font-medium text-blush underline underline-offset-2">Shop Sale</div>
                </div>
            </a>
        </aside>

        {{-- Results --}}
        <div class="flex-1">
            {{-- Toolbar: result count left, sorting right --}}
            <div class="mb-7 flex flex-wrap items-center justify-between gap-4 border-b border-line pb-4">
                <div class="text-[14px] font-light text-muted-2">
                    @if($products->total() > 0)
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} results
                    @else
                        No results
                    @endif
                </div>
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    @foreach(request()->except(['sort', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="tc-sort" class="text-[13px] font-light text-muted">Sort</label>
                    <select id="tc-sort" name="sort" data-auto-submit class="border-b border-line-2 bg-transparent py-1.5 pr-6 text-[14px] font-normal text-ink outline-none transition-colors focus:border-blush">
                        <option value="popular" @selected($filters['sort'] === 'popular')>Most popular</option>
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="price-asc" @selected($filters['sort'] === 'price-asc')>Price: low to high</option>
                        <option value="price-desc" @selected($filters['sort'] === 'price-desc')>Price: high to low</option>
                        <option value="rating" @selected($filters['sort'] === 'rating')>Top rated</option>
                    </select>
                    <noscript><button type="submit" class="border border-line-2 px-3 py-1.5 text-[13px]">Go</button></noscript>
                </form>
            </div>

            {{-- Applied filters, each removable --}}
            @if($activeFilters->isNotEmpty())
                <div class="mb-6 flex flex-wrap items-center gap-2.5">
                    @foreach($activeFilters as $key => $value)
                        <a href="{{ $clearUrl([$key]) }}" class="flex items-center gap-2 border border-line-2 px-3 py-1.5 text-[13px] font-light transition-colors hover:border-blush hover:text-blush">
                            {{ ucfirst($key) }}: {{ $value }} <span class="text-[14px] leading-none">×</span>
                        </a>
                    @endforeach
                    <a href="{{ $clearUrl($activeFilters->keys()->all()) }}" class="text-[13px] font-light text-blush underline underline-offset-2">Clear all</a>
                </div>
            @endif

            @if($products->isEmpty())
                <div class="border border-line bg-cream-3 px-6 py-20 text-center">
                    <div class="text-[20px] font-normal">Nothing matches those filters</div>
                    <div class="mt-2 text-[14px] font-light text-muted-2">Try a different size or price range.</div>
                    <a href="{{ route('listing') }}" class="tc-link mt-5 inline-block">Browse everything</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-x-7 gap-y-10 md:grid-cols-3">
                    @foreach($products as $p)
                        @include('partials.product-card', ['p' => $p, 'h' => 'h-[300px] sm:h-[380px]'])
                    @endforeach
                </div>
                @include('partials.pagination', ['paginator' => $products])
            @endif
        </div>
    </div>
@endsection
