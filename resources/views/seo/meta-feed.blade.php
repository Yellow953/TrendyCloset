<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
    <title>{{ config('seo.brand') }} product catalogue</title>
    <link>{{ url('/') }}</link>
    <description>{{ config('seo.description') }}</description>
@foreach($items as $item)
    <item>
        <g:id>{{ $item['id'] }}</g:id>
        <title>{{ $item['title'] }}</title>
        <description>{{ $item['description'] }}</description>
        <link>{{ $item['link'] }}</link>
        <g:image_link>{{ $item['image_link'] }}</g:image_link>
        @foreach($item['additional_image_link'] ?? [] as $image)
        <g:additional_image_link>{{ $image }}</g:additional_image_link>
        @endforeach
        <g:availability>{{ $item['availability'] }}</g:availability>
        <g:condition>{{ $item['condition'] }}</g:condition>
        <g:price>{{ $item['price'] }}</g:price>
        @if(!empty($item['sale_price']))
        <g:sale_price>{{ $item['sale_price'] }}</g:sale_price>
        @endif
        <g:brand>{{ $item['brand'] }}</g:brand>
        <g:gender>{{ $item['gender'] }}</g:gender>
        <g:age_group>{{ $item['age_group'] }}</g:age_group>
        @if(!empty($item['color']))
        <g:color>{{ $item['color'] }}</g:color>
        @endif
        @if(!empty($item['size']))
        <g:size>{{ $item['size'] }}</g:size>
        @endif
        <g:mpn>{{ $item['mpn'] }}</g:mpn>
        @if(!empty($item['product_type']))
        <g:product_type>{{ $item['product_type'] }}</g:product_type>
        @endif
    </item>
@endforeach
</channel>
</rss>
