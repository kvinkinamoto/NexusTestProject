@extends('storefront::public.layouts.storefront')

@section('title', 'Мій кабінет — Активувати подарунок — Nexus Store')

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">Мій кабінет</h1>

        <div class="grid lg:grid-cols-[240px_1fr] gap-10">
            @include('user::public.partials.account-sidebar', ['active' => 'redeem-gift'])

            <div class="max-w-md">
                <h2 class="font-semibold text-lg mb-2">Активувати подарунковий код</h2>
                <p class="text-sm text-neutral-500 mb-5">Введіть код, який отримали від того, хто зробив вам подарунок.</p>

                @if(session('status'))
                    <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('account.redeem-gift.store') }}" class="space-y-4">
                    @csrf
                    <input
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        placeholder="Наприклад, ABCDEFGHIJ"
                        class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-neutral-900/10"
                        autofocus
                    />
                    @error('code')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="w-full h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">
                        Активувати
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
