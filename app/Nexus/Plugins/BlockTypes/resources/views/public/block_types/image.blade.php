<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <figure class="ph aspect-[16/9] rounded-3xl overflow-hidden">
        @if($data->image ?? null)
            <img src="/{{ ltrim($data->image, '/') }}" alt="{{ $data->caption ?? '' }}" class="size-full object-cover">
        @endif
    </figure>
    @if($data->caption ?? null)
        <figcaption class="text-center text-sm text-neutral-500 mt-3">{{ $data->caption }}</figcaption>
    @endif
</section>
