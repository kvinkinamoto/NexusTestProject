<section class="nexus-block nexus-block--text">
    @if($data->heading ?? null)
        <h2 class="nexus-block__heading">{{ $data->heading }}</h2>
    @endif
    @if($data->body ?? null)
        <div class="nexus-block__body">{{ $data->body }}</div>
    @endif
</section>
