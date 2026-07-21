{{-- Every meta tag the storefront emits. $seo is the scoped App\Support\Seo
     instance the controller configured, supplied by a view composer — pages
     never write meta tags themselves. --}}
<title>{{ $seo->metaTitle() }}</title>
<meta name="description" content="{{ $seo->metaDescription() }}">
<meta name="robots" content="{{ $seo->metaRobots() }}">
<link rel="canonical" href="{{ $seo->metaCanonical() }}">

{{-- Open Graph — how the page renders when pasted into a chat or social post --}}
<meta property="og:type" content="{{ $seo->ogType() }}">
<meta property="og:site_name" content="{{ config('seo.brand_full') }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">
<meta property="og:title" content="{{ $seo->metaTitle() }}">
<meta property="og:description" content="{{ $seo->metaDescription() }}">
<meta property="og:url" content="{{ $seo->metaCanonical() }}">
<meta property="og:image" content="{{ $seo->metaImage() }}">
<meta property="og:image:alt" content="{{ $seo->metaTitle() }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->metaTitle() }}">
<meta name="twitter:description" content="{{ $seo->metaDescription() }}">
<meta name="twitter:image" content="{{ $seo->metaImage() }}">
@if(config('seo.twitter'))
    <meta name="twitter:site" content="{{ config('seo.twitter') }}">
@endif

@foreach(array_filter(config('seo.verification', [])) as $engine => $token)
    <meta name="{{ ['google' => 'google-site-verification', 'bing' => 'msvalidate.01', 'pinterest' => 'p:domain_verify'][$engine] ?? $engine }}" content="{{ $token }}">
@endforeach

{{-- One @graph per page: the store, the site, and whatever this page is about.
     UNESCAPED_SLASHES keeps the URLs readable to anyone reading the source. --}}
<script type="application/ld+json">{!! json_encode($seo->jsonLd(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
