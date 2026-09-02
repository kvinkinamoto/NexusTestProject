import collapse from '@alpinejs/collapse';

/**
 * Livewire v3 bundles and starts its own Alpine.js instance (via
 * @livewireScripts). Importing/starting a second `alpinejs` package copy
 * here causes a "multiple instances of Alpine" conflict where directives
 * are wired to Livewire's instance but stores/plugins registered on our
 * own import never reach it. Hooking `alpine:init` instead reaches
 * whichever Alpine instance is actually running on the page.
 */
document.addEventListener('alpine:init', () => {
    Alpine.plugin(collapse);

    Alpine.store('sidebar', {
        mobileOpen: false,
        collapsed: false,

        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        },

        toggleCollapsed() {
            this.collapsed = !this.collapsed;
        },
    });

    Alpine.store('theme', {
        theme: 'light',

        init() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            this.theme = savedTheme || systemTheme;
            this.updateTheme();
        },

        toggle() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            this.updateTheme();
        },

        updateTheme() {
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
        },
    });
});
