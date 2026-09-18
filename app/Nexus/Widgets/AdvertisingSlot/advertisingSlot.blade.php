@if($ad)
    <div class="ad-slot" data-ad-id="{{ $ad->id }}">
        @if($ad->type === \App\Nexus\Modules\Advertising\Enums\AdvertisingType::Banner)
            <a href="{{ route('advertising.click', $ad) }}" rel="sponsored noopener nofollow" target="_blank">
                <img src="{{ asset($ad->image) }}" alt="{{ $ad->title }}" class="mx-auto max-w-full rounded-lg" />
            </a>
        @else
            {{-- $ad->content_html is admin-entered embed code (AdSense unit or
                 other trusted third-party snippet) — same trust level as any
                 other admin-authored HTML in this app, not user input. --}}
            {!! $ad->content_html !!}
        @endif
    </div>
@endif
