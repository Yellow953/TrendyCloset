@props([
    'label',
    'value',
    'max' => null,
    'display' => null,
    'note' => null,
    'href' => null,
    'delay' => 0,
])

{{-- One labelled horizontal bar — the shape every breakdown on the reporting
     pages uses: category revenue, status mix, stock on hand, funnel steps. --}}
@php
    $width = ($max ?? 0) > 0 ? max(round(($value / $max) * 100, 1), $value > 0 ? 1 : 0) : 0;
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif class="group block">
    <div class="flex items-baseline justify-between gap-3 text-[12.5px]">
        <span class="truncate font-normal text-slate-600 group-hover:text-slate-800">{{ $label }}</span>
        <span class="ad-figure shrink-0 font-medium">{{ $display ?? number_format($value) }}</span>
    </div>

    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
        <div class="ad-bar-row h-full rounded-full bg-slate-900"
             style="width: {{ $width }}%; animation-delay: {{ $delay }}ms"></div>
    </div>

    @if($note)
        <div class="mt-1 text-[11px] font-normal text-slate-400">{{ $note }}</div>
    @endif
</{{ $tag }}>
