{{-- Back-office paginator: compact and numeric, with the range spelled out.
     The storefront's round pills would be too loud on a dense table. --}}
@if($paginator->hasPages())
    <nav aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-3.5">
        <p class="text-[12.5px] font-normal text-slate-400">
            Showing <span class="ad-figure text-slate-800">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            of <span class="ad-figure text-slate-800">{{ number_format($paginator->total()) }}</span>
        </p>

        <div class="flex items-center gap-1.5">
            @if($paginator->onFirstPage())
                <span class="ad-btn ad-btn-sm cursor-default opacity-40">←</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ad-btn ad-btn-sm" aria-label="Previous page">←</a>
            @endif

            @foreach($paginator->onEachSide(1)->links()->elements as $element)
                @if(is_string($element))
                    <span class="px-1.5 text-[12px] text-slate-400">{{ $element }}</span>
                @else
                    @foreach($element as $page => $url)
                        @if($page == $paginator->currentPage())
                            <span aria-current="page" class="ad-figure flex h-8 min-w-8 items-center justify-center rounded-lg bg-slate-800 px-2 text-[12.5px] font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="ad-figure flex h-8 min-w-8 items-center justify-center rounded-lg border border-slate-200 px-2 text-[12.5px] transition-colors hover:border-slate-900 hover:text-slate-900">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ad-btn ad-btn-sm" aria-label="Next page">→</a>
            @else
                <span class="ad-btn ad-btn-sm cursor-default opacity-40">→</span>
            @endif
        </div>
    </nav>
@endif
