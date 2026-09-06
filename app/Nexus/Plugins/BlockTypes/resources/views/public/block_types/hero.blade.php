<section class="nexus-block nexus-block--hero">
    @if($data->image ?? null)
        <img class="nexus-block__image" src="/{{ ltrim($data->image, '/') }}" alt="{{ $data->headline ?? '' }}">
    @endif
    @if($data->headline ?? null)
        <h2 class="nexus-block__headline">{{ $data->headline }}</h2>
    @endif
    @if($data->subheadline ?? null)
        <p class="nexus-block__subheadline">{{ $data->subheadline }}</p>
    @endif
    @if($data->cta_label ?? null)
        <a class="nexus-block__cta" href="{{ $data->cta_url ?? '#' }}">{{ $data->cta_label }}</a>
    @endif
</section>
