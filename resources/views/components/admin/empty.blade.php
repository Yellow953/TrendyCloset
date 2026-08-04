{{-- `icon` is a name from <x-admin.icon>, not a glyph. --}}
@props(['icon' => 'search', 'title' => 'Nothing here yet', 'body' => null])

<div class="flex flex-col items-center px-6 py-16 text-center">
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-1 ring-slate-200/70 ring-inset">
        <x-admin.icon :name="$icon" class="h-[22px] w-[22px]" />
    </span>
    <p class="mt-4 text-[15px] font-medium">{{ $title }}</p>
    @if($body)<p class="mt-1.5 max-w-[42ch] text-[13px] leading-relaxed font-normal text-slate-500">{{ $body }}</p>@endif
    @isset($aside)<div class="mt-5 w-full max-w-[54ch]">{{ $aside }}</div>@endisset
    @if(trim($slot) !== '')<div class="mt-5 flex flex-wrap justify-center gap-2.5">{{ $slot }}</div>@endif
</div>
