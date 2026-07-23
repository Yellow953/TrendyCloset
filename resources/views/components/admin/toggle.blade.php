@props(['name', 'label', 'checked' => false, 'hint' => null])

{{-- A checkbox that reads as a switch. The hidden 0 means an unticked box
     still posts a value, so `$request->boolean()` sees the difference between
     "off" and "not on the form at all". --}}
<label class="flex cursor-pointer items-start gap-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))
           {{ $attributes }} class="mt-0.5 h-4 w-4 shrink-0 accent-slate-900">
    <span>
        <span class="block text-[13px] font-medium text-slate-800">{{ $label }}</span>
        @if($hint)<span class="mt-0.5 block text-[12px] leading-relaxed font-normal text-slate-400">{{ $hint }}</span>@endif
    </span>
</label>
