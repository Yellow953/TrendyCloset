{{--
    Auth form field — label + input + inline validation message.
    $name, $label; optional $type, $value, $autocomplete, $autofocus, $placeholder.
--}}
@php
    $type ??= 'text';
    $value ??= null;
    $autocomplete ??= null;
    $autofocus ??= false;
    $placeholder ??= '';
@endphp

<div>
    <label for="{{ $name }}" class="mb-2 block text-[11px] font-medium tracking-[0.16em] text-muted-2 uppercase">
        {{ $label }}
    </label>

    <input id="{{ $name }}"
           name="{{ $name }}"
           type="{{ $type }}"
           value="{{ $value }}"
           placeholder="{{ $placeholder }}"
           @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if ($autofocus) autofocus @endif
           required
           class="tc-input @error($name) border-blush @enderror">

    @error($name)
        <p class="mt-2 text-[12.5px] font-light text-blush">{{ $message }}</p>
    @enderror
</div>
