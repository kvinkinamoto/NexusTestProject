<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="relative rounded-3xl overflow-hidden ph aspect-[16/9] sm:aspect-[21/9]">
        @if($data->image ?? null)
            <img src="/{{ ltrim($data->image, '/') }}" alt="{{ $data->headline ?? '' }}" class="absolute inset-0 size-full object-cover">
        @endif
        <div class="absolute inset-0 bg-black/30 flex items-center justify-center text-center px-6">
            <div>
                @if($data->headline ?? null)
                    <h1 class="font-outfit text-white text-3xl sm:text-5xl font-bold">{{ $data->headline }}</h1>
                @endif
                @if($data->subheadline ?? null)
                    <p class="text-white/85 mt-3 max-w-lg mx-auto text-sm sm:text-base">{{ $data->subheadline }}</p>
                @endif
                @if($data->cta_label ?? null)
                    <a href="{{ $data->cta_url ?? '#' }}" class="mt-6 inline-flex h-12 px-8 items-center justify-center rounded-full bg-white text-neutral-900 text-sm font-semibold hover:bg-neutral-100">{{ $data->cta_label }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
