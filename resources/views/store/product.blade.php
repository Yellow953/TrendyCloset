@extends('layouts.storefront')

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
        <div data-gallery class="flex w-full flex-col-reverse gap-4 sm:flex-row lg:w-[54%] lg:flex-none">
            @if($gallery->count() > 1)
                <div class="flex flex-row gap-3 sm:flex-col">
                    @foreach($gallery as $g)
                        <button type="button" data-gallery-thumb data-full="{{ $g->url }}"
                                class="h-[110px] w-[92px] flex-none overflow-hidden bg-cream transition sm:h-[168px] sm:w-[138px] {{ $loop->first ? 'is-active' : '' }}">
                            <img src="{{ $g->url }}" alt="{{ $product->name }} view {{ $loop->iteration }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="relative flex-1">
                <div data-zoom class="relative h-[480px] w-full cursor-zoom-in overflow-hidden bg-cream sm:h-[620px]">
                    @if($product->image_url)
                        <img data-gallery-main src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @endif
                </div>
                @if($product->badge_label)
                    <div class="pointer-events-none absolute left-4 top-4 bg-blush px-3 py-1.5 text-[12px] font-medium tracking-[0.04em] text-white">{{ $product->badge_label }}</div>
                @endif
            </div>
        </div>

        {{-- Purchase panel --}}
        <div class="flex flex-1 flex-col gap-5">
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
                                    <span class="block min-w-[64px] border border-line-2 px-4 py-3 text-center text-[14.5px] transition-colors peer-checked:border-blush peer-checked:text-blush {{ $v->in_stock ? 'hover:border-blush' : 'text-faint line-through' }}">{{ $v->size }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" data-clear-target="variant_id" class="mt-2.5 flex items-center gap-1.5 text-[13.5px] font-light text-muted-2 transition-colors hover:text-blush">
                            <span class="text-[15px] leading-none">×</span> Clear
                        </button>
                    </div>
                @endif

                @if($inStock)
                    <div class="inline-flex w-fit bg-cream-3 px-3 py-1.5 text-[13.5px] font-medium text-jade">{{ $stockLeft }} in stock</div>
                @else
                    <div class="inline-flex w-fit bg-cream-2 px-3 py-1.5 text-[13.5px] font-medium text-blush">Out of stock</div>
                @endif

                <div class="flex flex-wrap items-stretch gap-3">
                    <div data-qty class="flex items-center border border-line-2">
                        <button type="button" data-qty-down aria-label="Decrease quantity" class="px-4 py-3.5 text-[18px] leading-none transition-colors hover:text-blush">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity"
                               class="w-14 border-x border-line-2 py-3.5 text-center text-[15px] font-medium outline-none [appearance:textfield] focus:border-blush [&::-webkit-inner-spin-button]:appearance-none">
                        <button type="button" data-qty-up aria-label="Increase quantity" class="px-4 py-3.5 text-[18px] leading-none transition-colors hover:text-blush">+</button>
                    </div>

                    <button type="submit" name="action" value="cart" @disabled(! $inStock)
                            class="flex-1 bg-blush px-8 py-3.5 text-[14.5px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-ink disabled:cursor-not-allowed disabled:bg-faint">
                        {{ $inStock ? 'Add To Bag' : 'Sold Out' }}
                    </button>
                </div>

                <button type="submit" name="action" value="buy" @disabled(! $inStock)
                        class="w-full bg-ink py-4 text-[14.5px] font-medium tracking-[0.06em] text-white transition-colors hover:bg-blush disabled:cursor-not-allowed disabled:bg-faint">
                    Buy Now
                </button>
            </form>

            <div class="flex flex-wrap items-center gap-4">
                <form method="POST" action="{{ route('product.favorite', $product) }}" data-async data-favorite-form>
                    @csrf
                    <button type="submit" aria-pressed="{{ $favorited ? 'true' : 'false' }}"
                            class="flex items-center gap-2 text-[14px] font-light text-muted-2 transition-colors hover:text-blush aria-pressed:text-blush aria-pressed:[&_svg]:fill-current">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px]"><path d="M12 20.5 4.6 13.3a4.5 4.5 0 1 1 6.4-6.3l1 1 1-1a4.5 4.5 0 1 1 6.4 6.3Z"/></svg>
                        <span data-favorite-label>{{ $favorited ? 'Saved to favourites' : 'Add to favourites' }}</span>
                    </button>
                </form>
                @if($inStock && $stockLeft <= 10)
                    <span class="text-[13.5px] font-light text-blush">Only {{ $stockLeft }} left</span>
                @endif
            </div>

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
        <div data-sticky-buy class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 shadow-[0_-8px_24px_rgba(43,37,35,.10)] backdrop-blur">
            <div class="mx-auto flex max-w-[1280px] items-center gap-4 px-5 py-3 md:px-10">
                <div class="hidden h-[54px] w-[46px] flex-none overflow-hidden bg-cream sm:block">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover">
                    @endif
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
                                class="hidden border border-line-2 bg-white px-3 py-2.5 text-[14px] outline-none focus:border-blush sm:block">
                            @foreach($variants as $v)
                                <option value="{{ $v->id }}" @disabled(! $v->in_stock) @selected($firstAvailable?->is($v))>
                                    {{ $v->size }}{{ $v->in_stock ? '' : ' — sold out' }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity"
                           class="hidden w-16 border border-line-2 py-2.5 text-center text-[14px] font-medium outline-none [appearance:textfield] focus:border-blush md:block [&::-webkit-inner-spin-button]:appearance-none">
                    <button type="submit" name="action" value="cart"
                            class="whitespace-nowrap bg-blush px-6 py-3 text-[14px] font-medium tracking-[0.04em] text-white transition-colors hover:bg-ink">Add To Bag</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Full-width detail section, centred --}}
    <section data-tabs class="border-y border-line bg-cream-3 px-5 py-14 md:px-10">
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

    {{-- Frequently asked. Built in StoreController::productFaqs() from this
         piece's real sizes, colours and stock, and published as FAQPage
         structured data — which is only legitimate because the answers render
         here, on the page, for a shopper to read. --}}
    @if(!empty($faqs))
        <section class="px-5 py-14 md:px-10">
            <h2 class="tc-heading">Frequently asked</h2>
            <span class="tc-heading-rule"></span>
            <div class="mx-auto mt-9 max-w-[820px]">
                @foreach($faqs as $faq)
                    <details class="group/q border-b border-line">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-5 text-left text-[16px] font-medium">
                            <h3 class="text-[16px] font-medium">{{ $faq['question'] }}</h3>
                            <span class="text-[20px] leading-none text-muted">
                                <span class="group-open/q:hidden">+</span><span class="hidden group-open/q:inline">−</span>
                            </span>
                        </summary>
                        <p class="pb-5 pr-10 text-[15px] font-light leading-[1.85] text-muted-3">{{ $faq['answer'] }}</p>
                    </details>
                @endforeach
                <p class="mt-6 text-[14px] font-light text-muted">
                    Still unsure? <a href="{{ route('contact') }}" class="tc-link">Ask us</a> — we reply within 24 hours.
                </p>
            </div>
        </section>
    @endif

    {{-- Related --}}
    @if($related->isNotEmpty())
        <div class="px-5 py-14 md:px-10">
            <h2 class="tc-heading">You may also like</h2>
            <span class="tc-heading-rule"></span>
            <div class="mt-9 grid grid-cols-2 gap-x-7 gap-y-10 md:grid-cols-4">
                @foreach($related as $p)
                    @include('partials.product-card', ['p' => $p, 'h' => 'h-[280px] sm:h-[360px]'])
                @endforeach
            </div>
        </div>
    @endif
@endsection
