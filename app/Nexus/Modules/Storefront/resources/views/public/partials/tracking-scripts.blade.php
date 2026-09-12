{{--
    Reads the Analytics/Advertising modules' own #[Setting]s (see
    App\Nexus\Modules\{Analytics,Advertising}\Models\*.php — each is a
    settings-only module, no admin-manageable records of its own) and
    prints whichever official tracking snippets those settings enable.
    Both modules default 'enabled' to true but every ID defaults to blank,
    so with nothing configured this partial renders nothing at all.

    $position controls which half of each vendor's snippet to print — GTM
    and Meta Pixel both split into a <head> part and a noscript part that
    must sit right after the opening <body> tag:
      @include('storefront::public.partials.tracking-scripts', ['position' => 'head'])
      @include('storefront::public.partials.tracking-scripts', ['position' => 'body'])
--}}
@php
    $analyticsEnabled = \Nodex\Nexus\Services\SettingsBuilder::get('analytics', 'enabled', true);
    $ga4Id = $analyticsEnabled ? trim((string) \Nodex\Nexus\Services\SettingsBuilder::get('analytics', 'ga4_measurement_id')) : '';
    $gtmId = $analyticsEnabled ? trim((string) \Nodex\Nexus\Services\SettingsBuilder::get('analytics', 'gtm_container_id')) : '';

    $adsEnabled = \Nodex\Nexus\Services\SettingsBuilder::get('advertising', 'enabled', true);
    $fbPixelId = $adsEnabled ? trim((string) \Nodex\Nexus\Services\SettingsBuilder::get('advertising', 'facebook_pixel_id')) : '';
    $googleAdsId = $adsEnabled ? trim((string) \Nodex\Nexus\Services\SettingsBuilder::get('advertising', 'google_ads_conversion_id')) : '';

    // GA4 and Google Ads both run on the same gtag.js loader — one script
    // tag configured for every id present, per Google's own multi-tag
    // guidance, instead of loading gtag.js twice.
    $gtagIds = array_values(array_filter([$ga4Id, $googleAdsId]));
@endphp
@if(($position ?? 'head') === 'head')
    @if($gtmId)
        {{-- Google Tag Manager --}}
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @endif

    @if(!empty($gtagIds))
        {{-- Google gtag.js (GA4 / Google Ads) --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gtagIds[0] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            @foreach($gtagIds as $gtagId)
                gtag('config', '{{ $gtagId }}');
            @endforeach
        </script>
    @endif

    @if($fbPixelId)
        {{-- Meta Pixel --}}
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $fbPixelId }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1" alt="" /></noscript>
    @endif
@else
    @if($gtmId)
        {{-- Google Tag Manager (noscript) --}}
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
@endif
