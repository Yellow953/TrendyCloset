@props([
    'series',
    'format' => 'number',
    'height' => '190px',
])

{{-- A column chart from a Stats::series() collection. CSS and inline heights,
     no library: the bars are divs, the tooltip is a group-hover, and the whole
     thing degrades to a row of shapes with no script at all. --}}
@php
    $peak = max($series->max('value') ?? 0, 1);
    $money = $format === 'money';
@endphp

<div {{ $attributes }}>
    <div class="flex items-end gap-1.5" style="height: {{ $height }}">
        @foreach($series as $i => $point)
            <div class="group relative flex flex-1 flex-col items-center justify-end self-stretch">
                <div class="pointer-events-none absolute bottom-full z-10 mb-2 hidden whitespace-nowrap rounded-md bg-slate-800 px-2.5 py-1.5 text-[11px] text-white group-hover:block">
                    {{ $point['label'] }} ·
                    <span class="ad-figure">
                        {{ $money ? \App\Models\Product::money($point['value']) : number_format($point['value']) }}
                    </span>
                </div>

                <div class="ad-bar w-full rounded-t-[3px] transition-colors {{ $point['value'] > 0 ? 'bg-slate-900 group-hover:bg-slate-800' : 'bg-slate-100' }}"
                     style="height: {{ $point['value'] > 0 ? max(round(($point['value'] / $peak) * 100), 4) : 2 }}%; animation-delay: {{ $i * 35 }}ms"></div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 flex gap-1.5 border-t border-slate-100 pt-2.5">
        @foreach($series as $point)
            <div class="flex-1 truncate text-center text-[10px] font-normal text-slate-400">{{ $point['short'] }}</div>
        @endforeach
    </div>
</div>
