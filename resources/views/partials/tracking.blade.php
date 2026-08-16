{{-- Marketing analytics: the Meta pixel and GA4. Each renders only when its own
     id is set, so local and staging report into neither.

     $tracking is the scoped App\Support\Tracking the controller described events
     on, supplied by a view composer. Both destinations get the same facts,
     spelled the way each one expects. Events that happen after render (add to
     bag, favourite) arrive in JSON and are fired by app.js. --}}
@php
    $metaPixelId = \App\Support\Tracking::metaPixelId();
    $ga4Id = \App\Support\Tracking::ga4Id();
@endphp

@if($metaPixelId)
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window,document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');

        fbq('init', @json($metaPixelId));
        fbq('track', 'PageView');
        @foreach($tracking->metaEvents() as $event)
        fbq('track', @json($event['name']), @json((object) $event['params']));
        @endforeach
    </script>
    <noscript><img height="1" width="1" style="display:none" alt=""
        src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"></noscript>
@endif

@if($ga4Id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($ga4Id));
        @foreach($tracking->ga4Events() as $event)
        gtag('event', @json($event['name']), @json((object) $event['params']));
        @endforeach
    </script>
@endif
