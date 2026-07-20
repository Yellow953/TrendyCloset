@php
    $h = $h ?? 'h-[300px]';
    $stars = $p['stars'] ?? 5;
@endphp
<a href="{{ route('product') }}" class="group block">
    <div class="relative {{ $h }} overflow-hidden bg-cream">
        <img src="{{ $p['img'] }}" alt="{{ $p['name'] }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @if(!empty($p['badge']))
            <div class="pointer-events-none absolute left-3 top-3 bg-blush px-2 py-1 text-[12px] font-medium text-white">{{ $p['badge'] }}</div>
        @endif
    </div>
    <div class="mt-3.5 text-[15.5px] font-normal leading-[1.4]">{{ $p['name'] }}</div>
    <div class="mt-1 text-[13px] tracking-[2px] text-gold">{{ str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) }}</div>
    <div class="mt-1">
        @if(!empty($p['was']))
            <span class="text-[14px] font-light text-faint line-through">{{ $p['was'] }}</span>
        @endif
        <span class="text-[16px] font-semibold text-blush">{{ $p['now'] }}</span>
    </div>
</a>
