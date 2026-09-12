@extends('storefront::public.layouts.storefront')

@section('title', 'Мій кабінет — Профіль — STYLE')

@section('content')
    @php($user = auth()->user())
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">Мій кабінет</h1>

        @if(session('status'))
            <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        <div class="grid lg:grid-cols-[240px_1fr] gap-10">
            @include('user::public.partials.account-sidebar', ['active' => 'profile'])

            <div>
                <section class="pb-8 mb-8 border-b border-neutral-100">
                    <h2 class="font-semibold text-lg mb-5">Особисті дані</h2>
                    <form method="POST" action="{{ route('account.profile.update') }}" class="grid sm:grid-cols-2 gap-4 max-w-xl">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Ім'я</label>
                            <input name="name" value="{{ old('name', $user->name) }}" type="text" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Прізвище</label>
                            <input name="last_name" value="{{ old('last_name', $user->last_name) }}" type="text" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Email</label>
                            <input name="email" value="{{ old('email', $user->email) }}" type="email" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Телефон</label>
                            <input name="phone" value="{{ old('phone', $user->phone) }}" type="tel" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Дата народження</label>
                            <input name="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}" type="date" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="h-12 px-8 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">Зберегти зміни</button>
                        </div>
                    </form>
                </section>

                <section>
                    <h2 class="font-semibold text-lg mb-5">Зміна пароля</h2>
                    <form method="POST" action="{{ route('account.password.update') }}" class="grid sm:grid-cols-2 gap-4 max-w-xl">
                        @csrf
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-1.5">Поточний пароль</label>
                            <input name="current_password" type="password" placeholder="••••••••" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Новий пароль</label>
                            <input name="password" type="password" placeholder="••••••••" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Підтвердження паролю</label>
                            <input name="password_confirmation" type="password" placeholder="••••••••" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="h-12 px-8 rounded-full border border-neutral-900 text-sm font-semibold hover:bg-neutral-900 hover:text-white transition">Оновити пароль</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
