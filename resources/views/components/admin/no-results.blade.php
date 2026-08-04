{{--
    The other empty state: rows exist, the filters just excluded them all. It
    names the conditions in play and each chip links to the same list without
    that one, so widening the search never means re-reading the form above.
--}}
@props(['noun' => 'results', 'filters' => [], 'reset' => null])

<x-admin.empty icon="search-x" :title="'No '.$noun.' match'"
               :body="$filters
                    ? 'Nothing fits every condition below. Drop one to widen the search.'
                    : 'Nothing fits the filters currently applied.'">
    @if($filters)
        <x-slot:aside>
            <div class="flex flex-wrap justify-center gap-2">
                @foreach($filters as $filter)
                    <a href="{{ $filter['url'] }}" class="ad-chip group"
                       aria-label="Remove filter {{ $filter['label'] }}: {{ $filter['value'] }}">
                        <span class="font-normal text-slate-400">{{ $filter['label'] }}</span>
                        <span class="max-w-[22ch] truncate">{{ $filter['value'] }}</span>
                        <x-admin.icon name="close" class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-colors group-hover:text-slate-900" />
                    </a>
                @endforeach
            </div>
        </x-slot:aside>
    @endif

    @if($reset)
        <a href="{{ $reset }}" class="ad-btn">Clear all filters</a>
    @endif

    {{ $slot }}
</x-admin.empty>
