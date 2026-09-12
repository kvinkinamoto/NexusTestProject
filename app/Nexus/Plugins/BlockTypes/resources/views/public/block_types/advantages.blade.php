@php
    $advItems = [];
    for ($i = 1; $i <= 4; $i++) {
        $title = $data->{"item{$i}_title"} ?? null;
        if ($title) {
            $advItems[] = ['title' => $title, 'text' => $data->{"item{$i}_text"} ?? null];
        }
    }
    $advIcons = [
        'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5V14.25m0 0V9m0 5.25h13.5m-13.5 0V9m17.25 5.25V9a1.5 1.5 0 0 0-1.5-1.5h-1.5m3 6.75V9m-3 0H4.5',
        'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
        'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'M8.25 12h.008v.008H8.25V12Zm3.75 0h.008v.008H12V12Zm3.75 0h.008v.008h-.008V12ZM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z',
    ];
@endphp
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    @if($data->heading ?? null)
        <h2 class="font-outfit text-2xl sm:text-3xl font-bold text-center mb-10">{{ $data->heading }}</h2>
    @endif
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($advItems as $i => $item)
            <div class="text-center px-4">
                <div class="mx-auto size-14 rounded-full bg-sand-100 flex items-center justify-center mb-4">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $advIcons[$i % count($advIcons)] }}" /></svg>
                </div>
                <p class="font-semibold text-sm">{{ $item['title'] }}</p>
                @if($item['text'])
                    <p class="text-xs text-neutral-500 mt-1">{{ $item['text'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
