{{-- Paginator styled to match the storefront rather than Laravel's default.
     $paginator is a LengthAwarePaginator. --}}
@if($paginator->hasPages())
    <nav aria-label="Pagination" class="mt-12 flex flex-wrap items-center justify-center gap-2.5 text-[14px] font-normal">
        @if($paginator->onFirstPage())
            <span aria-hidden="true" class="flex h-11 w-11 items-center justify-center rounded-full border border-line text-faint">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
               class="flex h-11 w-11 items-center justify-center rounded-full border border-line-2 transition-colors hover:border-blush hover:text-blush">←</a>
        @endif

        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if($page === $paginator->currentPage())
                <span aria-current="page" class="flex h-11 w-11 items-center justify-center rounded-full bg-blush font-medium text-white">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="flex h-11 w-11 items-center justify-center rounded-full border border-line-2 transition-colors hover:border-blush hover:text-blush">{{ $page }}</a>
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
               class="flex h-11 w-11 items-center justify-center rounded-full border border-line-2 transition-colors hover:border-blush hover:text-blush">→</a>
        @else
            <span aria-hidden="true" class="flex h-11 w-11 items-center justify-center rounded-full border border-line text-faint">→</span>
        @endif
    </nav>
@endif
