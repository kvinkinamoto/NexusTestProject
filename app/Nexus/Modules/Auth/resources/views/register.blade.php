<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Реєстрація — STYLE</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/storefront.js'])
</head>
<body class="bg-white text-neutral-900 antialiased font-sans">

<div class="min-h-screen grid lg:grid-cols-2">
    <div class="hidden lg:block relative ph order-2 lg:order-1">
        <svg class="absolute inset-0 m-auto size-32 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-8.25V15M4.5 4.5h15A1.5 1.5 0 0 1 21 6v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        <div class="absolute bottom-12 left-12 right-12 text-white">
            <p class="text-xs font-semibold tracking-[0.2em] uppercase text-white/80 mb-2">Приєднуйтесь</p>
            <h2 class="font-outfit text-3xl font-bold">Знижка 10% на перше замовлення</h2>
        </div>
    </div>

    <div class="flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12 order-1 lg:order-2">
        <a href="{{ route('shop.home') }}" class="flex flex-col leading-none mb-8">
            <span class="font-outfit text-2xl font-bold tracking-tight">STYLE</span>
            <span class="text-[10px] tracking-[0.25em] text-neutral-400 font-medium">CLOTHING STORE</span>
        </a>

        <div class="max-w-sm w-full mx-auto lg:mx-0" x-data="registerForm()">
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold">Створити акаунт</h1>
            <p class="text-neutral-500 text-sm mt-2">Заповніть форму, щоб зареєструватися.</p>

            <template x-if="errors.length">
                <div class="mt-4 text-sm text-red-600 bg-red-50 rounded-lg px-4 py-3 space-y-1">
                    <template x-for="err in errors" :key="err"><p x-text="err"></p></template>
                </div>
            </template>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Ім'я</label>
                    <input x-model="form.name" required type="text" placeholder="Іван" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Email</label>
                    <input x-model="form.email" required type="email" placeholder="ivan@example.com" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Пароль</label>
                    <div class="relative">
                        <input x-model="form.password" :type="showPass ? 'text' : 'password'" required placeholder="Мінімум 8 символів" class="w-full h-12 rounded-xl border border-neutral-300 px-4 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                        <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400" aria-label="Показати пароль">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Підтвердження паролю</label>
                    <input x-model="form.password_confirmation" :type="showPass ? 'text' : 'password'" required placeholder="Повторіть пароль" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                </div>
                <label class="flex items-start gap-2.5 text-xs text-neutral-500">
                    <input required type="checkbox" class="size-4 rounded border-neutral-300 mt-0.5">
                    Я погоджуюсь з умовами використання та політикою конфіденційності
                </label>
                <button type="submit" :disabled="busy" class="w-full h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">
                    <span x-show="!busy">Зареєструватися</span>
                    <span x-show="busy" x-cloak>Реєстрація...</span>
                </button>
            </form>

            <p class="text-center text-sm text-neutral-500 mt-6">Вже маєте акаунт? <a href="{{ route('login') }}" class="font-semibold text-neutral-900 hover:underline">Увійти</a></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('registerForm', () => ({
        form: { name: '', email: '', password: '', password_confirmation: '' },
        showPass: false,
        busy: false,
        errors: [],
        async submit() {
            this.errors = [];
            if (this.form.password !== this.form.password_confirmation) {
                this.errors = ['Паролі не співпадають'];
                return;
            }
            this.busy = true;
            try {
                await window.storefrontFetch('/register', { method: 'POST', body: this.form });
                window.location = '{{ route('shop.home') }}';
            } catch (e) {
                this.errors = e.data?.errors ? Object.values(e.data.errors).flat() : [e.message];
            } finally {
                this.busy = false;
            }
        },
    }));
});
</script>
</body>
</html>
