{{-- Live search results. Served by StoreController::searchSuggest and swapped
     in under the header search field as the shopper types. --}}
<div class="mx-auto max-w-[760px] px-5 py-2 md:px-10">
    @forelse($products as $p)
        <a href="{{ route('product', $p) }}" class="group flex items-center gap-3.5 border-b border-line py-3 last:border-0">
            <span class="tc-media block h-[64px] w-[64px] shrink-0 rounded-field">
                <x-img :src="$p->image_url" :alt="$p->name" sizes="64px" class="h-full w-full object-cover" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[14.5px] font-normal transition-colors group-hover:text-blush">{{ $p->name }}</span>
                @if($p->color_run->isNotEmpty())
                    <span class="mt-0.5 block truncate text-[12.5px] font-light text-muted-2">{{ $p->color_run->implode(' · ') }}</span>
                @endif
            </span>
            <span class="shrink-0 text-right text-[14.5px]">
                <span class="font-semibold text-blush">{{ $p->price_label }}</span>
                @if($p->compare_label)
                    <span class="block text-[12.5px] font-light text-muted-2 line-through">{{ $p->compare_label }}</span>
                @endif
            </span>
        </a>
    @empty
        <div class="py-5 text-center text-[14px] font-light text-muted-2">Nothing matches “{{ $term }}”.</div>
    @endforelse

    @if($total > $products->count())
        <a href="{{ route('listing', ['q' => $term]) }}" class="tc-link block py-3.5 text-center text-[13.5px]">View all {{ $total }} results</a>
    @endif
</div>
