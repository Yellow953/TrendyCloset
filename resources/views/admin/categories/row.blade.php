{{-- One row of the category tree, recursing into its children. `$row` is the
     closure defined in the index view, so depth stays a rendering concern. --}}
<tr>
    <td>
        <div class="flex items-center gap-3" style="padding-left: {{ $depth * 22 }}px">
            @if($depth > 0)
                <span class="text-[12px] text-slate-200" aria-hidden="true">└</span>
            @endif

            <div class="h-9 w-9 shrink-0 overflow-hidden rounded-md border border-slate-100 bg-slate-100">
                @if($category->image_url)
                    <img src="{{ $category->image_url }}" alt="" class="h-full w-full object-cover">
                @endif
            </div>

            <div class="min-w-0">
                <a href="{{ route('admin.categories.edit', $category) }}" class="block truncate font-medium hover:text-slate-900">{{ $category->name }}</a>
                <span class="mt-0.5 block truncate text-[11.5px] font-normal text-slate-400">/shop/{{ $category->slug }}</span>
            </div>
        </div>
    </td>

    <td class="text-right">
        <span class="ad-figure font-normal text-slate-600">{{ $category->products->count() }}</span>
    </td>

    <td class="ad-figure text-right font-normal text-slate-400">{{ $category->position }}</td>

    <td>
        <span class="ad-badge {{ $category->is_active ? 'ad-badge-good' : 'ad-badge-neutral' }}">
            {{ $category->is_active ? 'Live' : 'Hidden' }}
        </span>
    </td>

    <td>
        <div class="flex items-center justify-end gap-1.5">
            <a href="{{ route('listing', $category) }}" target="_blank" rel="noopener" class="ad-btn ad-btn-sm" title="View on the shop">↗</a>
            <a href="{{ route('admin.categories.edit', $category) }}" class="ad-btn ad-btn-sm">Edit</a>
            <button type="button" data-modal-open="delete-category-{{ $category->id }}"
                    class="ad-btn ad-btn-sm text-rose-600 hover:border-rose-600 hover:text-rose-600" title="Delete">✕</button>
        </div>
    </td>
</tr>

@foreach($category->children as $child)
    {!! $row($child, $depth + 1) !!}
@endforeach
