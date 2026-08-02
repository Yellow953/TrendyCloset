@props([
    'lines',
    'buckets',
    'height' => 240,
    // The last bucket is still filling up. Left solid it reads as a collapse,
    // when all it means is that the day is not over.
    'partial' => true,
    'partialLabel' => 'in progress',
])

{{-- Several series on ONE shared scale. Never a second y-axis: two scales on one
     frame let any two lines be made to cross wherever you like, which is the
     fastest way to read a chart wrong. Views dwarfing bags and orders is the
     true shape of a shop, and the totals in the legend carry the exact numbers
     the squashed lines cannot.

     `lines` is [['label','color','values'=>[...],'total'=>n], …]; `buckets` is
     the shared x-axis from Stats::series(). --}}
@php
    $count = max($buckets->count(), 1);
    $peak = 0.0;

    foreach ($lines as $line) {
        $peak = max($peak, ...(count($line['values']) ? $line['values'] : [0]));
    }

    $scale = $peak > 0 ? $peak : 1;
    $w = max($count - 1, 1);
    $h = 100;
    $top = 6; // headroom so the peak never touches the frame

    $points = function (array $values) use ($w, $h, $top, $scale, $count) {
        return collect($values)
            ->map(fn ($v, $i) => round($i * ($w / max($count - 1, 1)), 3).','.round($h - ($v / $scale) * ($h - $top), 2))
            ->all();
    };

    // Solid up to the last complete bucket, dashed into the one still running.
    $dashLast = $partial && $count > 2;
    $plot = fn (array $values) => implode(' ', $dashLast
        ? array_slice($points($values), 0, $count - 1)
        : $points($values));
    $tail = fn (array $values) => implode(' ', array_slice($points($values), -2));

    // A handful of gridlines, labelled, so a low line can still be read off.
    $ticks = [$scale, $scale * 0.5, 0];
@endphp

<div {{ $attributes }}>
    {{-- Legend. Always present for two or more series, and each entry carries
         its own total, so identity never rests on colour alone. --}}
    <div class="mb-5 flex flex-wrap gap-x-7 gap-y-2.5">
        @foreach($lines as $line)
            <div class="flex items-baseline gap-2">
                <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $line['color'] }}"></span>
                <span class="text-[12.5px] font-normal text-slate-500">{{ $line['label'] }}</span>
                <span class="ad-figure text-[15px] font-semibold text-slate-900">{{ number_format($line['total']) }}</span>
            </div>
        @endforeach

        @if($dashLast)
            <span class="ml-auto self-center text-[11px] font-normal text-slate-400">
                Dashed = {{ $partialLabel }}
            </span>
        @endif
    </div>

    <div class="flex gap-2">
        {{-- y-axis --}}
        <div class="flex shrink-0 flex-col justify-between text-right text-[10px] font-normal text-slate-400"
             style="height: {{ $height }}px">
            @foreach($ticks as $tick)
                <span class="ad-figure leading-none">{{ number_format($tick) }}</span>
            @endforeach
        </div>

        <div class="relative min-w-0 flex-1" style="height: {{ $height }}px">
            {{-- Recessive gridlines --}}
            <div class="absolute inset-0 flex flex-col justify-between">
                @foreach($ticks as $tick)
                    <span class="block border-t border-slate-100"></span>
                @endforeach
            </div>

            <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none"
                 class="absolute inset-0 h-full w-full overflow-visible" aria-hidden="true">
                @foreach($lines as $line)
                    <polyline points="{{ $plot($line['values']) }}"
                              fill="none" stroke="{{ $line['color'] }}" stroke-width="2"
                              stroke-linejoin="round" stroke-linecap="round"
                              vector-effect="non-scaling-stroke" />

                    @if($dashLast)
                        <polyline points="{{ $tail($line['values']) }}"
                                  fill="none" stroke="{{ $line['color'] }}" stroke-width="2"
                                  stroke-dasharray="3 3" stroke-linecap="round"
                                  vector-effect="non-scaling-stroke" />
                    @endif
                @endforeach
            </svg>

            {{-- Crosshair and tooltip: one column per bucket, each a target far
                 wider than the 2px marks it reads. --}}
            <div class="absolute inset-0 flex">
                @foreach($buckets as $i => $bucket)
                    <div class="group relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-px -translate-x-1/2 bg-slate-300 group-hover:block"></span>

                        <div class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 hidden -translate-x-1/2 rounded-md bg-slate-800 px-2.5 py-2 text-[11px] whitespace-nowrap text-white group-hover:block
                                    {{ $i < 2 ? 'left-0 translate-x-0' : '' }} {{ $i > $count - 3 ? 'left-auto right-0 translate-x-0' : '' }}">
                            <div class="mb-1 font-medium text-slate-300">
                                {{ $bucket['label'] }}@if($dashLast && $i === $count - 1)<span class="font-normal text-slate-400"> · {{ $partialLabel }}</span>@endif
                            </div>
                            @foreach($lines as $line)
                                <div class="flex items-center gap-2">
                                    <span class="inline-block h-2 w-2 shrink-0 rounded-full" style="background: {{ $line['color'] }}"></span>
                                    <span class="text-slate-300">{{ $line['label'] }}</span>
                                    <span class="ad-figure ml-auto font-semibold">{{ number_format($line['values'][$i] ?? 0) }}</span>
                                </div>
                            @endforeach
                        </div>

                        @foreach($lines as $line)
                            <span class="pointer-events-none absolute left-1/2 hidden h-2.5 w-2.5 -translate-x-1/2 translate-y-1/2 rounded-full ring-2 ring-white group-hover:block"
                                  style="bottom: {{ round((($line['values'][$i] ?? 0) / $scale) * (100 - $top), 2) }}%; background: {{ $line['color'] }}"></span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- x-axis. Thinned to roughly a dozen labels so they never collide. --}}
    @php $every = max(1, (int) ceil($count / 12)); @endphp
    <div class="mt-2.5 flex gap-0 pl-[34px]">
        @foreach($buckets as $i => $bucket)
            <div class="min-w-0 flex-1 text-center text-[10px] font-normal text-slate-400">
                {{ $i % $every === 0 ? $bucket['short'] : '' }}
            </div>
        @endforeach
    </div>
</div>
