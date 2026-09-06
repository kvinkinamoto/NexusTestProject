<figure class="nexus-block nexus-block--image">
    @if($data->image ?? null)
        <img class="nexus-block__image" src="/{{ ltrim($data->image, '/') }}" alt="{{ $data->caption ?? '' }}">
    @endif
    @if($data->caption ?? null)
        <figcaption class="nexus-block__caption">{{ $data->caption }}</figcaption>
    @endif
</figure>
