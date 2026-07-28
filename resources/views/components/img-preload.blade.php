{{-- Preload hint for the image a page's LCP depends on.

     Only worth it for an image the parser would otherwise discover late (a hero
     behind the stylesheet), and only ever one per page. `sizes` MUST match the
     matching <x-img> exactly, or the browser preloads one candidate and then
     downloads another. Push it into the layout's `head` stack. --}}
@props([
    'src' => null,
    'sizes' => '100vw',
])
@if($src)
    @php($srcset = \App\Support\Img::srcset($src))
    <link rel="preload" as="image" fetchpriority="high" href="{{ $src }}"
          @if($srcset) imagesrcset="{{ $srcset }}" imagesizes="{{ $sizes }}" @endif>
@endif
