{{-- Product card. $p is an App\Models\Product — eager-load `images` and
     `variants` (the hover rail quick-adds the first in-stock variant).
     $h overrides the image height. $fav pre-fills the heart where the caller
     already knows the piece is saved (the favourites page). $imgSizes describes
     the slot the card sits in, for the responsive image ladder — the default
     matches the two/three-up grids the card is normally laid out in. It is not
     called $sizes because @include inherits the caller's scope, and both the
     listing and the PDP have a $sizes of their own (the size facet). --}}
@php
    // Square by default, because that is the shape product photographs are
    // cropped to on upload (ImageStore::SQUARE) — a fixed pixel height would
    // letterbox or crop them a second time, differently at every breakpoint.
    // $h is still honoured for a caller that genuinely needs a fixed frame.
    $h = $h ?? 'aspect-square';
    $fav = $fav ?? false;
    $imgSizes = $imgSizes ?? '(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw';
    $variant = $p->relationLoaded('variants') ? $p->default_variant : null;

    // What the piece comes in, under the price — read off the loaded variants,
    // so it is what is actually sellable rather than a claim.
    //
    // Whichever of the two carries the variety is the one worth the row: a
    // piece offered in five colours says that with swatches, and one offered in
    // a single colour says it with its size run instead. Showing both on a
    // 200px card turns the price into a footnote. Anything past the fifth
    // collapses into "+N" so every card in a grid keeps the same height.
    $colors = $p->relationLoaded('variants') ? $p->color_run : collect();
    $sizes = $p->relationLoaded('variants') && $colors->count() < 2 ? $p->size_run : collect();
    $extraColors = max($colors->count() - 5, 0);
    $extraSizes = max($sizes->count() - 5, 0);
@endphp
<div class="group relative">
    <div class="tc-card-media relative {{ $h }} overflow-hidden rounded-xl bg-cream">
        <a href="{{ route('product', $p) }}" class="block h-full w-full" aria-label="{{ $p->name }}">
            <x-img :src="$p->image_url" :alt="$p->name" :sizes="$imgSizes"
                   class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
        </a>

        @if($p->badge_label)
            <div class="pointer-events-none absolute left-3 top-3 bg-blush px-2 py-1 text-[12px] font-medium text-white">{{ $p->badge_label }}</div>
        @endif

        @if($p->relationLoaded('variants') && ! $p->in_stock)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-ink/80 py-2 text-center text-[12px] font-medium tracking-[0.12em] text-white">SOLD OUT</div>
        @endif

        {{-- Hover rail: save, quick-add, view. The three actions deal in from
             the right one after another (see [data-card-rail] in app.css), and
             stay reachable by keyboard via focus-within. --}}
        <div data-card-rail class="absolute right-3 top-3 flex flex-col gap-2">
            <form method="POST" action="{{ route('product.favorite', $p) }}" data-async data-favorite-form>
                @csrf
                <button type="submit" aria-pressed="{{ $fav ? 'true' : 'false' }}" class="tc-card-action aria-pressed:text-blush aria-pressed:[&_svg]:fill-current" title="Save to favourites" aria-label="Save {{ $p->name }} to favourites">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px]"><path d="M12 20.5 4.6 13.3a4.5 4.5 0 1 1 6.4-6.3l1 1 1-1a4.5 4.5 0 1 1 6.4 6.3Z"/></svg>
                </button>
            </form>

            <form method="POST" action="{{ route('cart.add') }}" data-async>
                @csrf
                <input type="hidden" name="variant_id" value="{{ $variant?->id }}">
                <button type="submit" class="tc-card-action" @disabled(! $variant)
                        title="{{ $variant ? 'Add to bag — '.$variant->label : 'Sold out' }}"
                        aria-label="Add {{ $p->name }} to bag">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px]"><path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
                </button>
            </form>

            <a href="{{ route('product', $p) }}" class="tc-card-action" title="View product" aria-label="View {{ $p->name }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px]"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.6"/></svg>
            </a>
        </div>
    </div>

    <a href="{{ route('product', $p) }}" class="mt-3.5 block text-[15.5px] font-normal leading-[1.4] transition-colors hover:text-blush">{{ $p->name }}</a>
    <div class="mt-1 text-[13px] tracking-[2px] text-gold">{{ str_repeat('★', $p->rating) . str_repeat('☆', 5 - $p->rating) }}</div>
    <div class="mt-1">
        @if($p->compare_label)
            <span class="text-[14px] font-light text-faint line-through">{{ $p->compare_label }}</span>
        @endif
        <span class="text-[16px] font-semibold text-blush">{{ $p->price_label }}</span>
    </div>

    @if($sizes->isNotEmpty())
        <div class="mt-2 flex flex-wrap items-center gap-1.5"
             aria-label="{{ $colors->isNotEmpty() ? $colors->first().'. ' : '' }}Sizes: {{ $sizes->implode(', ') }}">
            {{-- One colour still earns its dot, leading the size run: it is the
                 shade of the piece in the photograph, said in 14 pixels. --}}
            @if($colors->isNotEmpty())
                <x-swatch :color="$colors->first()" class="mr-0.5" />
            @endif
            @foreach($sizes->take(5) as $size)
                <span class="border border-line px-1.5 py-0.5 text-[11px] font-light leading-none tracking-[0.06em] text-muted-2">{{ $size }}</span>
            @endforeach
            @if($extraSizes)
                <span class="text-[12px] font-light text-muted">+{{ $extraSizes }}</span>
            @endif
        </div>
    @elseif($colors->isNotEmpty())
        <div class="mt-2 flex items-center gap-1.5" aria-label="Colours: {{ $colors->implode(', ') }}">
            @foreach($colors->take(5) as $color)
                <x-swatch :color="$color" />
            @endforeach
            @if($extraColors)
                <span class="text-[12px] font-light text-muted">+{{ $extraColors }}</span>
            @endif
        </div>
    @endif
</div>
