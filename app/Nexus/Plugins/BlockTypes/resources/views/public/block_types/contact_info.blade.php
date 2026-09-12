<section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="space-y-6">
        @if($data->address ?? null)
            <div class="flex items-start gap-4">
                <div class="size-11 rounded-full bg-sand-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </div>
                <div>
                    <p class="font-semibold text-sm">Адреса магазину</p>
                    <p class="text-sm text-neutral-500 mt-0.5">{{ $data->address }}</p>
                </div>
            </div>
        @endif
        @if($data->phone ?? null)
            <div class="flex items-start gap-4">
                <div class="size-11 rounded-full bg-sand-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a11.25 11.25 0 0 1-5.169-5.169l1.293-.97a1.125 1.125 0 0 0 .417-1.173L8.963 3.102a1.125 1.125 0 0 0-1.091-.852H6.5A2.25 2.25 0 0 0 2.25 6.75Z" /></svg>
                </div>
                <div>
                    <p class="font-semibold text-sm">Телефон</p>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $data->phone) }}" class="text-sm text-neutral-500 hover:text-neutral-900 mt-0.5 block">{{ $data->phone }}</a>
                </div>
            </div>
        @endif
        @if($data->email ?? null)
            <div class="flex items-start gap-4">
                <div class="size-11 rounded-full bg-sand-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                </div>
                <div>
                    <p class="font-semibold text-sm">Email</p>
                    <a href="mailto:{{ $data->email }}" class="text-sm text-neutral-500 hover:text-neutral-900 mt-0.5 block">{{ $data->email }}</a>
                </div>
            </div>
        @endif
        @if($data->hours ?? null)
            <div class="flex items-start gap-4">
                <div class="size-11 rounded-full bg-sand-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <p class="font-semibold text-sm">Графік роботи</p>
                    <p class="text-sm text-neutral-500 mt-0.5">{{ $data->hours }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="ph aspect-[4/3] sm:aspect-[16/10] rounded-2xl overflow-hidden mt-8 relative">
        <svg class="size-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
        @if($data->map_label ?? null)
            <span class="absolute bottom-3 left-3 bg-white/90 text-xs font-medium px-3 py-1.5 rounded-full">{{ $data->map_label }}</span>
        @endif
    </div>
</section>
