@php
    $galleryImages = array_filter([$data->image_1 ?? null, $data->image_2 ?? null, $data->image_3 ?? null, $data->image_4 ?? null]);
@endphp
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    @if($data->heading ?? null)
        <h2 class="font-outfit text-2xl sm:text-3xl font-bold text-center mb-10">{{ $data->heading }}</h2>
    @endif
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($galleryImages as $image)
            <div class="ph aspect-square rounded-2xl overflow-hidden">
                <img src="/{{ ltrim($image, '/') }}" alt="" class="size-full object-cover">
            </div>
        @endforeach
    </div>
</section>
