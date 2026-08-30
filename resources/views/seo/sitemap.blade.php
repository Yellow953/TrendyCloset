<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @if($url['lastmod'])
        <lastmod>{{ $url['lastmod']->format(DATE_ATOM) }}</lastmod>
        @endif
        <changefreq>{{ $url['freq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
        @foreach($url['images'] ?? [] as $image)
        <image:image><image:loc>{{ $image }}</image:loc></image:image>
        @endforeach
    </url>
@endforeach
</urlset>
