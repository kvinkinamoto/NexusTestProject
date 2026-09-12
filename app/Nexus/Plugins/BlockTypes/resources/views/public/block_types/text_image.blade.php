@php($imageLeft = ($data->image_position ?? 'right') === 'left')
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div class="ph aspect-[4/3] rounded-3xl overflow-hidden {{ $imageLeft ? 'lg:order-1' : 'lg:order-2' }}">
            @if($data->image ?? null)
                <img src="/{{ ltrim($data->image, '/') }}" alt="{{ $data->heading ?? '' }}" class="size-full object-cover">
            @endif
        </div>
        <div class="{{ $imageLeft ? 'lg:order-2' : 'lg:order-1' }}">
            @if($data->heading ?? null)
                <h2 class="font-outfit text-2xl sm:text-3xl font-bold mb-4">{{ $data->heading }}</h2>
            @endif
            @if($data->body ?? null)
                <div class="text-neutral-600 text-sm sm:text-base leading-relaxed">{!! nl2br(e($data->body)) !!}</div>
            @endif
        </div>
    </div>
</section>
