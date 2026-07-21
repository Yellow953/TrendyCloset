{{-- Product card. $p is an App\Models\Product — eager-load `images` and
     `variants` (the hover rail quick-adds the first in-stock variant).
     $h overrides the image height. --}}
@php
    $h = $h ?? 'h-[300px]';
    $variant = $p->relationLoaded('variants') ? $p->default_variant : null;
@endphp
<div class="group relative">
    <div class="relative {{ $h }} overflow-hidden bg-cream">
        <a href="{{ route('product', $p) }}" class="block h-full w-full" aria-label="{{ $p->name }}">
            @if($p->image_url)
                <img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
            @endif
        </a>

        @if($p->badge_label)
            <div class="pointer-events-none absolute left-3 top-3 bg-blush px-2 py-1 text-[12px] font-medium text-white">{{ $p->badge_label }}</div>
        @endif

        @if($p->relationLoaded('variants') && ! $p->in_stock)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-ink/80 py-2 text-center text-[12px] font-medium tracking-[0.12em] text-white">SOLD OUT</div>
        @endif

        {{-- Hover rail: save, quick-add, view. Slides in from the right on
             hover, and stays reachable by keyboard via focus-within. --}}
        <div class="absolute right-3 top-3 flex translate-x-3 flex-col gap-2 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 group-focus-within:translate-x-0 group-focus-within:opacity-100">
            <form method="POST" action="{{ route('product.favorite', $p) }}">
                @csrf
                <button type="submit" class="tc-card-action" title="Save to favourites" aria-label="Save {{ $p->name }} to favourites">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px]"><path d="M12 20.5 4.6 13.3a4.5 4.5 0 1 1 6.4-6.3l1 1 1-1a4.5 4.5 0 1 1 6.4 6.3Z"/></svg>
                </button>
            </form>

            <form method="POST" action="{{ route('cart.add') }}">
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
</div>
