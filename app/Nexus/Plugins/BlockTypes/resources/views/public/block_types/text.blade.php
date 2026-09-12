<section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center">
    @if($data->heading ?? null)
        <h2 class="font-outfit text-2xl sm:text-3xl font-bold mb-4">{{ $data->heading }}</h2>
    @endif
    @if($data->body ?? null)
        <div class="text-neutral-600 text-sm sm:text-base leading-relaxed">{!! nl2br(e($data->body)) !!}</div>
    @endif
</section>
