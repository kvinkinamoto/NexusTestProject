<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="rounded-3xl bg-neutral-900 px-6 sm:px-12 py-12 text-center">
        @if($data->heading ?? null)
            <h2 class="font-outfit text-white text-2xl sm:text-3xl font-bold">{{ $data->heading }}</h2>
        @endif
        @if($data->button_label ?? null)
            <a href="{{ $data->button_url ?? '#' }}" class="mt-6 inline-flex h-12 px-8 items-center justify-center rounded-full bg-white text-neutral-900 text-sm font-semibold hover:bg-neutral-100">{{ $data->button_label }}</a>
        @endif
    </div>
</section>
