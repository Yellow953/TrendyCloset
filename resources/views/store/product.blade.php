@extends('layouts.storefront')

@php
    // The frame the main photograph fills, measured: 54% of the row from lg,
    // less the 138px thumbnail rail and its gap; full width below that. Defined
    // once because the preload below and the <x-img> in the gallery have to
    // state the same thing — disagree and the browser fetches one candidate and
    // then renders another.
    $mainImageSizes = '(min-width: 1024px) calc(54vw - 197px), (min-width: 768px) calc(100vw - 234px), (min-width: 640px) calc(100vw - 194px), calc(100vw - 40px)';
@endphp

@push('head')
    {{-- The main photograph is the LCP. --}}
    <x-img-preload :src="$product->image_url" :sizes="$mainImageSizes" />
@endpush

@php
    $inStock = $product->in_stock;
    $stockLeft = $variants->sum('stock');
    // Pre-select the first size that can actually be bought.
    $firstAvailable = $variants->first(fn ($v) => $v->in_stock);
@endphp

@section('content')
    <div class="px-5 pb-0 pt-5 text-[13px] font-light text-muted md:px-10">
        <a href="{{ route('home') }}" class="hover:text-blush">Home</a>
        @foreach($breadcrumb as $crumb)
            / <a href="{{ route('listing', $crumb) }}" class="hover:text-blush">{{ $crumb->name }}</a>
        @endforeach
        / <span class="text-ink">{{ $product->name }}</span>
    </div>

    <div class="flex flex-col gap-12 px-5 pb-14 pt-6 md:px-10 lg:flex-row lg:gap-16">
        {{-- Gallery: thumbnail rail + zoomable main image --}}
        <div data-gallery data-reveal="left" class="flex w-full flex-col-reverse gap-4 sm:flex-row lg:w-[54%] lg:flex-none">
            @if($gallery->count() > 1)
                <div class="flex flex-row gap-3 sm:flex-col">
                    @foreach($gallery as $g)
                        {{-- data-srcset travels with data-full so the main image
                             keeps its responsive ladder after a swap — setting
                             src alone would leave the old srcset winning. --}}
                        <button type="button" data-gallery-thumb data-full="{{ $g->url }}"
                                data-srcset="{{ \App\Support\Img::srcset($g->url) }}"
                                class="tc-media h-[92px] w-[92px] flex-none transition sm:h-[138px] sm:w-[138px] {{ $loop->first ? 'is-active' : '' }}">
                            <x-img :src="$g->url" :alt="$product->name.' view '.$loop->iteration" sizes="138px"
                                   class="h-full w-full object-cover" />
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="relative flex-1">
                {{-- Square, like the file itself: product photographs are cropped
                     to 1:1 on upload, so a fixed-height frame would crop them a
                     second time and differently at every breakpoint. --}}
                <div data-zoom class="tc-media relative aspect-square w-full rounded-panel">
                    {{-- The page's LCP. --}}
                    <x-img data-gallery-main :src="$product->image_url" :alt="$product->name" eager
                           :sizes="$mainImageSizes"
                           class="h-full w-full object-cover" />
                </div>
                @if($product->badge_label)
                    <div class="tc-badge pointer-events-none absolute left-4 top-4 px-3 py-1.5 tracking-[0.04em]">{{ $product->badge_label }}</div>
                @endif

                {{-- Actions on the photograph itself. A sibling of [data-zoom]
                     rather than a child, so moving onto a button leaves the
                     frame and drops the zoom instead of magnifying under it. --}}
                <div class="absolute right-4 top-4 z-10 flex flex-col gap-2.5">
                    <form method="POST" action="{{ route('product.favorite', $product) }}" data-async data-favorite-form>
                        @csrf
                        <button type="submit" aria-pressed="{{ $favorited ? 'true' : 'false' }}"
                                class="tc-card-action h-10 w-10 aria-pressed:text-blush aria-pressed:[&_svg]:fill-current"
                                title="{{ $favorited ? 'Saved to favourites' : 'Save to favourites' }}"
                                aria-label="Save {{ $product->name }} to favourites">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[19px] w-[19px]"><path d="M12 20.5 4.6 13.3a4.5 4.5 0 1 1 6.4-6.3l1 1 1-1a4.5 4.5 0 1 1 6.4 6.3Z"/></svg>
                        </button>
                    </form>

                    <button type="button" class="tc-card-action h-10 w-10"
                            data-share="{{ route('product', $product) }}" data-share-title="{{ $product->name }}"
                            title="Share" aria-label="Share {{ $product->name }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[19px] w-[19px]"><circle cx="18" cy="5" r="2.6"/><circle cx="6" cy="12" r="2.6"/><circle cx="18" cy="19" r="2.6"/><path d="m8.4 10.8 7.2-4.2M8.4 13.2l7.2 4.2"/></svg>
                    </button>

                    {{-- Zoom is a choice, not a surprise: on a touch screen an
                         armed frame pans instead of scrolling the page. --}}
                    <button type="button" data-zoom-toggle aria-pressed="false"
                            class="tc-card-action h-10 w-10 aria-pressed:bg-ink aria-pressed:text-white"
                            title="Zoom" aria-label="Toggle zoom on the photograph">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[19px] w-[19px]"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 4.5 4.5M8 10.5h5M10.5 8v5"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Purchase panel. data-reveal-children walks the panel top to bottom
             — category, name, price, then the form — as the page settles. --}}
        <div data-reveal-children class="flex flex-1 flex-col gap-5">
            @if($product->category)
                <div class="text-[13.5px] font-light text-muted">
                    Category: <a href="{{ route('listing', $product->category) }}" class="font-medium text-blush hover:underline">{{ $product->category->name }}</a>
                </div>
            @endif

            <h1 class="text-[30px] font-normal leading-[1.2] md:text-[38px]">{{ $product->name }}</h1>

            <div class="flex flex-wrap items-center gap-3">
                @if($product->compare_label)
                    <span class="text-[18px] font-light text-faint line-through">{{ $product->compare_label }}</span>
                @endif
                <span class="text-[30px] font-semibold text-blush">{{ $product->price_label }}</span>
                <span class="text-[14px] tracking-[2px] text-gold">{{ str_repeat('★', $product->rating) . str_repeat('☆', 5 - $product->rating) }}</span>
                @if($favoritesCountForProduct > 0)
                    <span class="text-[13.5px] font-light text-muted">(saved by {{ $favoritesCountForProduct }} {{ Str::plural('shopper', $favoritesCountForProduct) }})</span>
                @endif
            </div>

            {{-- Urgency, counted from the real add-to-bag log --}}
            @if($recentAdds > 0)
                <div class="flex items-center gap-2 text-[14.5px] font-medium text-blush">
                    <span>🔥</span> Selling fast — added to {{ $recentAdds }} {{ Str::plural('bag', $recentAdds) }} this week
                </div>
            @endif

            @if($product->description)
                <p class="max-w-[560px] text-[14.5px] font-light leading-[1.75] text-muted-3">{{ $product->description }}</p>
            @endif

            @if($colors->isNotEmpty())
                <div>
                    <div class="mb-2.5 text-[15px] font-medium">Colour <span class="font-light text-muted">— {{ $colors->implode(', ') }}</span></div>
                    <div class="flex gap-2.5">
                        @foreach($colors as $c)
                            <span title="{{ $c }}" class="h-[28px] w-[28px] rounded-full outline-2 outline-offset-2 outline-blush {{ \App\Support\Swatch::needsOutline($c) ? 'border border-line-2' : '' }}" style="background-color: {{ \App\Support\Swatch::hex($c) }}"></span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- One form, two submits: add to bag, or buy now (adds, then goes
                 straight to checkout). The radio carries the variant, so size
                 and stock are enforced by the same request. --}}
            {{-- data-async covers "Add To Bag"; "Buy Now" is left to submit
                 normally, since it has to navigate to checkout. --}}
            <form method="POST" action="{{ route('cart.add') }}" data-buy-form data-async class="flex flex-col gap-5">
                @csrf

                @if($variants->isNotEmpty())
                    <div>
                        <div class="mb-2.5 flex items-center justify-between">
                            <span class="text-[15px] font-medium">Size</span>
                            <a href="{{ route('policies', 'size-guide') }}" class="text-[13.5px] font-light text-muted-2 underline underline-offset-2 hover:text-blush">Size chart</a>
                        </div>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($variants as $v)
                                <label class="{{ $v->in_stock ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                                    <input type="radio" name="variant_id" value="{{ $v->id }}" class="peer sr-only"
                                        @checked($firstAvailable?->is($v))
                                        @disabled(! $v->in_stock)>
                                    <span class="tc-chip min-w-[64px] px-4 py-3 text-[14.5px] peer-checked:border-blush peer-checked:text-blush {{ $v->in_stock ? '' : 'text-faint line-through hover:border-line-2 hover:text-faint' }}">{{ $v->size }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" data-clear-target="variant_id" class="mt-2.5 flex items-center gap-1.5 text-[13.5px] font-light text-muted-2 transition-colors hover:text-blush">
                            <span class="text-[15px] leading-none">×</span> Clear
                        </button>
                    </div>
                @endif

                @if($inStock)
                    <div class="tc-badge bg-cream-3 px-3 py-1.5 text-[13.5px] text-jade">{{ $stockLeft }} in stock</div>
                @else
                    <div class="tc-badge bg-cream-2 px-3 py-1.5 text-[13.5px] text-blush">Out of stock</div>
                @endif

                <div class="flex flex-wrap items-stretch gap-3">
                    <div data-qty class="tc-stepper">
                        <button type="button" data-qty-down aria-label="Decrease quantity" class="px-4 py-3.5 text-[18px] leading-none transition-colors hover:text-blush">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity"
                               class="w-14 border-x border-line-2 py-3.5 text-center text-[15px] font-medium outline-none [appearance:textfield] focus:border-blush [&::-webkit-inner-spin-button]:appearance-none">
                        <button type="button" data-qty-up aria-label="Increase quantity" class="px-4 py-3.5 text-[18px] leading-none transition-colors hover:text-blush">+</button>
                    </div>

                    <button type="submit" name="action" value="cart" @disabled(! $inStock)
                            class="tc-btn-blush flex-1 py-3.5 text-[14.5px]">
                        {{ $inStock ? 'Add To Bag' : 'Sold Out' }}
                    </button>
                </div>

                <button type="submit" name="action" value="buy" @disabled(! $inStock)
                        class="tc-btn-dark w-full text-[14.5px]">
                    Buy Now
                </button>
            </form>

            @if($inStock && $stockLeft <= 10)
                <div class="text-[13.5px] font-light text-blush">Only {{ $stockLeft }} left</div>
            @endif

            <div class="flex flex-wrap gap-6 border-t border-line pt-5 text-[13px] font-light text-muted-2">
                <span>🚚 Free shipping over {{ \App\Models\Product::money(\App\Support\Cart::FREE_SHIPPING_THRESHOLD) }}</span>
                <span>↩ 30-day returns</span>
                <span>🔒 Secure checkout</span>
            </div>
        </div>
    </div>

    {{-- Sticky buy bar: slides up once the main Add To Bag scrolls out of view.
         Its size select stays in sync with the radios above (see app.js). --}}
    @if($inStock)
        <div data-sticky-buy class="fixed inset-x-0 bottom-0 z-30 rounded-t-panel border-t border-line bg-white/95 shadow-[0_-8px_24px_rgba(43,37,35,.10)] backdrop-blur">
            <div class="mx-auto flex max-w-[1280px] items-center gap-4 px-5 py-3 md:px-10">
                <div class="tc-media hidden h-[54px] w-[46px] flex-none rounded-field sm:block">
                    <x-img :src="$product->image_url" alt="" sizes="46px" class="h-full w-full object-cover" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-[14.5px] font-normal">{{ $product->name }}</div>
                    <div class="text-[14px]">
                        @if($product->compare_label)
                            <span class="font-light text-faint line-through">{{ $product->compare_label }}</span>
                        @endif
                        <span class="font-semibold text-blush">{{ $product->price_label }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('cart.add') }}" data-async class="flex items-center gap-2.5">
                    @csrf
                    @if($variants->isNotEmpty())
                        <select name="variant_id" data-sticky-size aria-label="Size"
                                class="tc-input tc-input-sm hidden w-auto bg-white text-[14px] sm:block">
                            @foreach($variants as $v)
                                <option value="{{ $v->id }}" @disabled(! $v->in_stock) @selected($firstAvailable?->is($v))>
                                    {{ $v->size }}{{ $v->in_stock ? '' : ' — sold out' }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity"
                           class="tc-input tc-input-sm hidden w-16 px-0 text-center text-[14px] font-medium [appearance:textfield] md:block [&::-webkit-inner-spin-button]:appearance-none">
                    <button type="submit" name="action" value="cart"
                            class="tc-btn-blush tc-btn-sm whitespace-nowrap px-6 py-3 text-[14px] tracking-[0.04em]">Add To Bag</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Full-width detail section, centred --}}
    <section data-tabs data-reveal="fade" class="border-y border-line bg-cream-3 px-5 py-14 md:px-10">
        <div class="mx-auto max-w-[1000px]">
            <div class="flex flex-wrap justify-center gap-8 border-b border-line-2 text-[13.5px] font-medium tracking-[0.1em]">
                <button type="button" data-tab="description" class="is-active -mb-px border-b-2 border-transparent pb-3 transition-colors hover:text-blush">DESCRIPTION</button>
                <button type="button" data-tab="details" class="-mb-px border-b-2 border-transparent pb-3 text-muted transition-colors hover:text-blush">ADDITIONAL INFORMATION</button>
                <button type="button" data-tab="shipping" class="-mb-px border-b-2 border-transparent pb-3 text-muted transition-colors hover:text-blush">SHIPPING &amp; RETURNS</button>
            </div>

            <div data-tab-panel="description" class="pt-8 text-center">
                <p class="mx-auto max-w-[760px] text-[15.5px] font-light leading-[1.9] text-muted-3">
                    {{ $product->description }}
                    @if($product->category)
                        Filed under <a href="{{ route('listing', $product->category) }}" class="tc-link">{{ $product->category->name }}</a>, and styled by Leila before it ever shipped.
                    @endif
                </p>
            </div>

            <div data-tab-panel="details" class="hidden pt-8">
                <dl class="mx-auto grid max-w-[760px] grid-cols-1 gap-x-10 gap-y-4 sm:grid-cols-2">
                    @if($product->category)
                        <div class="flex justify-between border-b border-line pb-3"><dt class="text-[14px] font-medium">Category</dt><dd class="text-[14px] font-light text-muted-2">{{ $product->category->name }}</dd></div>
                    @endif
                    @if($sizes->isNotEmpty())
                        <div class="flex justify-between border-b border-line pb-3"><dt class="text-[14px] font-medium">Sizes</dt><dd class="text-[14px] font-light text-muted-2">{{ $sizes->implode(', ') }}</dd></div>
                    @endif
                    @if($colors->isNotEmpty())
                        <div class="flex justify-between border-b border-line pb-3"><dt class="text-[14px] font-medium">Colour</dt><dd class="text-[14px] font-light text-muted-2">{{ $colors->implode(', ') }}</dd></div>
                    @endif
                    <div class="flex justify-between border-b border-line pb-3"><dt class="text-[14px] font-medium">Availability</dt><dd class="text-[14px] font-light text-muted-2">{{ $inStock ? $stockLeft.' in stock' : 'Out of stock' }}</dd></div>
                    <div class="flex justify-between border-b border-line pb-3"><dt class="text-[14px] font-medium">Rating</dt><dd class="text-[14px] font-light text-muted-2">{{ $product->rating }} / 5</dd></div>
                    @if($product->on_sale)
                        <div class="flex justify-between border-b border-line pb-3"><dt class="text-[14px] font-medium">Discount</dt><dd class="text-[14px] font-light text-blush">−{{ $product->discount_percent }}%</dd></div>
                    @endif
                </dl>
            </div>

            <div data-tab-panel="shipping" class="hidden pt-8 text-center">
                <p class="mx-auto max-w-[760px] text-[15.5px] font-light leading-[1.9] text-muted-3">
                    Free delivery on orders over {{ \App\Models\Product::money(\App\Support\Cart::FREE_SHIPPING_THRESHOLD) }}, otherwise {{ \App\Models\Product::money(\App\Support\Cart::STANDARD_SHIPPING) }} standard shipping (3–5 business days).
                    You have 30 days to return anything unworn with its tags attached — see our
                    <a href="{{ route('policies', 'returns') }}" class="tc-link">returns policy</a> for the details.
                </p>
            </div>
        </div>
    </section>

    {{-- Related --}}
    @if($related->isNotEmpty())
        <div class="py-14">
            <h2 data-reveal class="tc-heading">You may also like</h2>
            <span data-reveal class="tc-heading-rule"></span>
            <div data-carousel class="relative mt-9">
                <div data-carousel-track data-reveal-children class="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-px-5 scroll-smooth px-5 sm:gap-6 md:scroll-px-10 md:px-10">
                    @foreach($related as $p)
                        <div class="w-[47%] shrink-0 snap-start md:w-[31%] lg:w-[23.5%]">
                            @include('partials.product-card', [
                                'p' => $p,
                                'imgSizes' => '(min-width: 1024px) calc(23.5vw - 19px), (min-width: 768px) calc(31vw - 25px), calc(47vw - 19px)',
                            ])
                        </div>
                    @endforeach
                </div>
                <button type="button" data-carousel-prev aria-label="Previous products" class="tc-arrow absolute left-2 top-[35%] -translate-y-1/2 md:left-3">&lsaquo;</button>
                <button type="button" data-carousel-next aria-label="Next products" class="tc-arrow absolute right-2 top-[35%] -translate-y-1/2 md:right-3">&rsaquo;</button>
            </div>
        </div>
    @endif
@endsection
