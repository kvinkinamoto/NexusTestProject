<section class="nexus-block nexus-block--cta">
    @if($data->heading ?? null)
        <h2 class="nexus-block__heading">{{ $data->heading }}</h2>
    @endif
    @if($data->button_label ?? null)
        <a class="nexus-block__button" href="{{ $data->button_url ?? '#' }}">{{ $data->button_label }}</a>
    @endif
</section>
