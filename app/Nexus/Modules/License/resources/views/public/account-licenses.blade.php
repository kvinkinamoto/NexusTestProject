@extends('storefront::public.layouts.storefront')

@section('title', __('Мій кабінет').' — '.__('Мої ліцензії').' — Nexus Store')

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">{{ __('Мій кабінет') }}</h1>

        <div class="grid lg:grid-cols-[240px_1fr] gap-10">
            @include('user::public.partials.account-sidebar', ['active' => 'licenses'])

            <div>
                <h2 class="font-semibold text-lg mb-5">{{ __('Мої ліцензії') }}</h2>

                @if(session('status'))
                    <div class="mb-5 rounded-xl bg-green-50 text-green-700 text-sm px-4 py-3">{{ session('status') }}</div>
                @endif

                @if($licenses->isEmpty())
                    <p class="text-sm text-neutral-500">{{ __("У вас ще немає ліцензій. Посилання на завантаження та ключ з'являться тут одразу після оплати замовлення.") }}</p>
                @else
                    <div class="space-y-5">
                        @foreach($licenses as $license)
                            <div class="border border-neutral-200 rounded-2xl p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                    <div>
                                        <p class="font-semibold">{{ $license->product?->displayName() ?? '—' }}</p>
                                        <p class="text-xs text-neutral-400 font-mono mt-1">{{ $license->license_key }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $license->isActive() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $license->isActive() ? __('Активна') : __('Відкликана') }}
                                        </span>
                                        @if($license->isActive() && $license->product?->releases->isNotEmpty())
                                            <a href="{{ $license->download_url }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-neutral-900 text-white text-xs font-semibold hover:bg-neutral-800">
                                                {{ __('Завантажити') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-xs text-neutral-500 mb-3">{{ __('Активовано :used з :max проєктів', ['used' => $license->activations->count(), 'max' => $license->max_activations]) }}</p>

                                @if($license->activations->isNotEmpty())
                                    <ul class="space-y-2 mb-3">
                                        @foreach($license->activations as $activation)
                                            <li class="flex items-center justify-between text-sm bg-neutral-50 rounded-lg px-3 py-2">
                                                <span class="truncate">{{ $activation->identifier }}</span>
                                                <form method="POST" action="{{ route('account.licenses.deactivate', ['license' => $license->id, 'activation' => $activation->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-500 hover:underline">{{ __('Зняти') }}</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if($license->isActive() && $license->activations->count() < $license->max_activations)
                                    <form method="POST" action="{{ route('account.licenses.activate', $license->id) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="identifier" required placeholder="{{ __('Домен або назва проєкту') }}" class="flex-1 h-10 rounded-lg border border-neutral-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                                        <button type="submit" class="px-4 h-10 rounded-lg border border-neutral-300 text-sm font-medium hover:bg-neutral-50">{{ __('Активувати') }}</button>
                                    </form>
                                    @error('identifier')
                                        <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
