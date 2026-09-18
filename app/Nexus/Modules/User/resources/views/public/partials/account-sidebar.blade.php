@php($user = auth()->user())
<aside class="hidden lg:block">
    <div class="flex items-center gap-3 pb-6 mb-6 border-b border-neutral-100">
        <div class="size-12 rounded-full bg-neutral-900 text-white flex items-center justify-center font-semibold">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        <div class="min-w-0">
            <p class="font-semibold text-sm truncate">{{ $user->name }}</p>
            <p class="text-xs text-neutral-500 truncate">{{ $user->email }}</p>
        </div>
    </div>
    <nav class="space-y-1 text-sm font-medium">
        <a href="{{ route('account.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'profile' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50' }}">
            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.964 0a9 9 0 1 0-11.964 0m11.964 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            Профіль
        </a>
        <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'orders' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50' }}">
            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375C2.754 3.75 2.25 4.254 2.25 4.875v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
            Мої замовлення
        </a>
        <a href="{{ route('shop.wishlist') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'wishlist' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50' }}">
            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
            Список бажань
        </a>
        <a href="{{ route('account.redeem-gift.show') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'redeem-gift' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50' }}">
            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v13.5m0-13.5a2.25 2.25 0 1 0-2.25-2.25 2.313 2.313 0 0 0 .659 1.591L12 8.25Zm0 0a2.25 2.25 0 1 1 2.25-2.25 2.313 2.313 0 0 1-.659 1.591L12 8.25ZM3.75 12h16.5M4.5 8.25h15a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
            Активувати подарунок
        </a>
        <a href="{{ route('logout') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-500 hover:bg-red-50 mt-4">
            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" /></svg>
            Вихід
        </a>
    </nav>
</aside>

<div class="lg:hidden flex gap-2 mb-6 overflow-x-auto no-scrollbar">
    <a href="{{ route('account.profile.edit') }}" class="shrink-0 px-4 py-2 rounded-full text-sm font-medium {{ $active === 'profile' ? 'bg-neutral-900 text-white' : 'border border-neutral-300' }}">Профіль</a>
    <a href="{{ route('account.orders') }}" class="shrink-0 px-4 py-2 rounded-full text-sm font-medium {{ $active === 'orders' ? 'bg-neutral-900 text-white' : 'border border-neutral-300' }}">Мої замовлення</a>
    <a href="{{ route('shop.wishlist') }}" class="shrink-0 px-4 py-2 rounded-full text-sm font-medium {{ $active === 'wishlist' ? 'bg-neutral-900 text-white' : 'border border-neutral-300' }}">Список бажань</a>
    <a href="{{ route('account.redeem-gift.show') }}" class="shrink-0 px-4 py-2 rounded-full text-sm font-medium {{ $active === 'redeem-gift' ? 'bg-neutral-900 text-white' : 'border border-neutral-300' }}">Активувати подарунок</a>
</div>
