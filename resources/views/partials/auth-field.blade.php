{{--
    Auth form field — label + input + inline validation message. Styled with the
    back-office `.ad-*` primitives so sign-in matches the admin it opens.
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
    <label for="{{ $name }}" class="ad-label">{{ $label }}</label>

    <input id="{{ $name }}"
           name="{{ $name }}"
           type="{{ $type }}"
           value="{{ $value }}"
           placeholder="{{ $placeholder }}"
           @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if ($autofocus) autofocus @endif
           required
           class="ad-input @error($name) border-rose-400 focus:border-rose-400 focus:ring-rose-100 @enderror">

    @error($name)
        <p class="ad-error">{{ $message }}</p>
    @enderror
</div>
