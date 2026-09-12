<section class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-neutral-50 rounded-2xl p-6 sm:p-8">
        @if($data->heading ?? null)
            <h2 class="font-semibold text-lg mb-6">{{ $data->heading }}</h2>
        @endif
        @if($data->form_slug ?? null)
            @nexusForm($data->form_slug)
        @endif
    </div>
</section>
