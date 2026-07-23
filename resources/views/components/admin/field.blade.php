@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'options' => null,
    'rows' => 4,
    'step' => null,
    'prefix' => null,
])

{{-- One labelled control, with its inline error. `options` turns it into a
     select; `type="textarea"` into a textarea. Everything else is an input. --}}
@php
    $current = old($name, $value);
    $invalid = $errors->has($name);
    $classes = 'ad-input'.($invalid ? ' border-rose-600 focus:border-rose-600 focus:ring-rose-600/12' : '').($prefix ? ' pl-7' : '');
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="ad-label">
            {{ $label }}
            @unless($required)<span class="font-normal text-slate-400">· optional</span>@endunless
        </label>
    @endif

    <div class="relative">
        @if($prefix)
            <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-[13px] text-slate-400">{{ $prefix }}</span>
        @endif

        @if($options !== null)
            <select id="{{ $name }}" name="{{ $name }}" @required($required)
                    {{ $attributes->except('class') }} class="{{ $classes }}">
                @unless($required)<option value="">—</option>@endunless
                @foreach($options as $key => $text)
                    <option value="{{ $key }}" @selected((string) $current === (string) $key)>{{ $text }}</option>
                @endforeach
            </select>
        @elseif($type === 'textarea')
            <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                      @required($required) {{ $attributes->except('class') }}
                      class="{{ $classes }} resize-y">{{ $current }}</textarea>
        @else
            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}"
                   placeholder="{{ $placeholder }}" @if($step) step="{{ $step }}" @endif
                   @required($required) {{ $attributes->except('class') }} class="{{ $classes }}">
        @endif
    </div>

    @error($name)
        <p class="ad-error">{{ $message }}</p>
    @else
        @if($hint)<p class="ad-hint">{{ $hint }}</p>@endif
    @enderror
</div>
