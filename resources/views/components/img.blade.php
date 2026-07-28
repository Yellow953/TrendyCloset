{{-- Storefront photograph.

     Renders nothing when there is no image, so callers do not need to guard.
     Resizable URLs (Unsplash) get a `srcset` ladder from App\Support\Img and
     download the size the slot actually occupies — pass `sizes` describing that
     slot in CSS terms, e.g. "(min-width: 768px) 25vw, 70vw".

     Everything is lazy and async-decoded unless `eager` says otherwise; reserve
     `eager` for the one image that is the page's LCP (the hero, the PDP's main
     photograph), which also gets fetchpriority="high". --}}
@props([
    'src' => null,
    'alt' => '',
    'sizes' => '100vw',
    'eager' => false,
])
@php
    $srcset = \App\Support\Img::srcset($src);
    $dimensions = \App\Support\Img::dimensions($src);
@endphp
@if($src)
    <img src="{{ $src }}" alt="{{ $alt }}"
         @if($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
         @if($dimensions) width="{{ $dimensions['width'] }}" height="{{ $dimensions['height'] }}" @endif
         loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
         @if($eager) fetchpriority="high" @endif
         {{ $attributes }}>
@endif
