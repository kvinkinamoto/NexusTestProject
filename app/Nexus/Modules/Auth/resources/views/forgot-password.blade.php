<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Відновлення пароля — STYLE</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/storefront.js'])
</head>
<body class="bg-white text-neutral-900 antialiased font-sans">

<div class="min-h-screen flex flex-col items-center justify-center px-6 py-12" x-data="forgotPasswordForm()">
    <a href="{{ route('shop.home') }}" class="flex flex-col leading-none items-center mb-10">
        <span class="font-outfit text-2xl font-bold tracking-tight">STYLE</span>
        <span class="text-[10px] tracking-[0.25em] text-neutral-400 font-medium">CLOTHING STORE</span>
    </a>

    <div class="max-w-sm w-full">
        <template x-if="!sent">
            <div>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-900 mb-6">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    Назад до входу
                </a>
                <h1 class="font-outfit text-2xl sm:text-3xl font-bold">Забули пароль?</h1>
                <p class="text-neutral-500 text-sm mt-2">Введіть email, і ми надішлемо посилання для відновлення пароля.</p>

                <p x-show="error" x-cloak class="mt-4 text-sm text-red-600 bg-red-50 rounded-lg px-4 py-3" x-text="error"></p>

                <form class="mt-8 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Email</label>
                        <input x-model="email" required type="email" placeholder="ivan@example.com" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                    </div>
                    <button type="submit" :disabled="busy" class="w-full h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">
                        <span x-show="!busy">Надіслати посилання</span>
                        <span x-show="busy" x-cloak>Надсилання...</span>
                    </button>
                </form>
            </div>
        </template>

        <template x-if="sent">
            <div class="text-center">
                <div class="mx-auto size-16 rounded-full bg-emerald-50 flex items-center justify-center">
                    <svg class="size-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                </div>
                <h1 class="font-outfit text-2xl font-bold mt-6">Перевірте пошту</h1>
                <p class="text-neutral-500 text-sm mt-2">Ми надіслали посилання для відновлення пароля на вашу електронну адресу.</p>
                <a href="{{ route('login') }}" class="mt-8 inline-flex h-12 px-8 items-center justify-center rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">Повернутись до входу</a>
            </div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('forgotPasswordForm', () => ({
        email: '',
        busy: false,
        sent: false,
        error: null,
        async submit() {
            this.busy = true;
            this.error = null;
            try {
                await window.storefrontFetch('{{ route('password.email') }}', { method: 'POST', body: { email: this.email } });
                this.sent = true;
            } catch (e) {
                this.error = e.data?.errors ? Object.values(e.data.errors).flat().join(' ') : e.message;
            } finally {
                this.busy = false;
            }
        },
    }));
});
</script>
</body>
</html>
