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

/**
 * Global "is this doing anything?" feedback for Livewire actions: whatever
 * [wire:click] element the user just clicked gets a dimmed/spinner state
 * for the duration of the request it triggers. Covers sort headers, row
 * actions, toggles, pagination etc. without touching each blade partial —
 * the dedicated save-button spinner (wire:loading wire:target="save") is
 * unaffected since that button doesn't carry wire:click itself.
 */
document.addEventListener('livewire:init', () => {
    let pendingEl = null;

    document.addEventListener('click', (e) => {
        pendingEl = e.target.closest('[wire\\:click]');
    }, true);

    Livewire.hook('request', ({ succeed, fail }) => {
        const el = pendingEl;
        pendingEl = null;
        if (!el) return;

        el.classList.add('nexus-wire-busy');
        const clear = () => el.classList.remove('nexus-wire-busy');
        succeed(clear);
        fail(clear);
    });
});
