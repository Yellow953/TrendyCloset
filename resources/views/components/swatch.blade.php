{{-- A colour chip. The hex comes from App\Support\Swatch, which falls back to a
     neutral rather than nothing — an invisible swatch reads as a missing colour.
     Pale shades get a stronger ring so they still have an edge on white. --}}
@props(['color' => null])
<span title="{{ $color }}"
      {{ $attributes->class([
          'h-3.5 w-3.5 shrink-0 rounded-full ring-1 ring-inset',
          \App\Support\Swatch::needsOutline($color) ? 'ring-line-2' : 'ring-black/10',
      ]) }}
      style="background-color: {{ \App\Support\Swatch::hex($color) }}"></span>
