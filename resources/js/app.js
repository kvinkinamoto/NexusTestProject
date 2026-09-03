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

/**
 * Choices.js fires a real 'change' event on the underlying <select> when a
 * choice is picked (see Choices' dispatchEvent/_triggerChange), which is
 * what a plain wire:model binding relies on to pick up the new value. In
 * practice that races Livewire's own DOM-sync-back for <select> elements —
 * it wins occasionally and resets the option back to blank right after
 * Choices set it correctly, discarding the pick client-side. Choices' change
 * event carries the authoritative value in event.detail.value regardless of
 * what the DOM ends up showing, so that's pushed into the component
 * explicitly via Livewire.find(...).set() instead of trusting the DOM read.
 * Delegated on `document` so it covers every [data-choices] select without
 * caring whether Choices was (re)initialized by this file or by the layouts'
 * own DOMContentLoaded script (see layouts/adminpanel.blade.php and
 * layouts/blank.blade.php, which still handle each element's first init).
 */
document.addEventListener('change', (e) => {
    if (!e.detail || typeof e.detail.value === 'undefined') return;

    const item = e.target.closest('[data-choices]');
    if (!item) return;

    const modelAttr = [...item.attributes].find((a) => a.name === 'wire:model' || a.name.startsWith('wire:model.'));
    const root = item.closest('[wire\\:id]');
    if (!modelAttr || !root) return;

    window.Livewire?.find(root.getAttribute('wire:id'))?.set(modelAttr.value, e.detail.value, modelAttr.name.includes('.live'));
});

/**
 * Re-init Choices.js on Livewire-rendered [data-choices] selects after every
 * component update. Choices wraps the original <select> in its own markup;
 * Livewire's morph diffs against the plain server-rendered <select> and
 * strips that wrapper back off on each update, so the widget needs rebuilding
 * every time — same parsing of the data-choices-* modifier attributes as the
 * layouts' own DOMContentLoaded init (see layouts/adminpanel.blade.php and
 * layouts/blank.blade.php), which still handles the very first render.
 * A once-initialized select is hidden (display:none) by Choices itself, so
 * that's used as the "already live" check instead of a separate registry.
 *
 * The <select> itself survives the morph (only Choices' wrapper around it is
 * stripped), so a prior Choices instance's event listeners are still live on
 * it — without cleanup, that orphaned instance and the freshly-created one
 * both react to the next selection, and the orphaned one (unaware anything
 * changed) stomps the value straight back. There's no reference to call that
 * instance's own .destroy() with, so the element is cloned instead — a clone
 * carries no listeners — before wiring up the new instance on the clone.
 */
document.addEventListener('livewire:init', () => {
    Livewire.hook('morphed', ({ el }) => {
        el.querySelectorAll('[data-choices]').forEach((original) => {
            if (getComputedStyle(original).display === 'none') return;

            const item = original.cloneNode(true);
            original.replaceWith(item);

            const choiceData = {};
            const attrs = item.attributes;
            if (attrs['data-choices-groups']) choiceData.placeholderValue = 'This is a placeholder set in the config';
            if (attrs['data-choices-search-false']) choiceData.searchEnabled = false;
            if (attrs['data-choices-search-true']) choiceData.searchEnabled = true;
            if (attrs['data-choices-removeItem']) choiceData.removeItemButton = true;
            if (attrs['data-choices-sorting-false']) choiceData.shouldSort = false;
            if (attrs['data-choices-sorting-true']) choiceData.shouldSort = true;
            if (attrs['data-choices-multiple-remove']) choiceData.removeItemButton = true;
            if (attrs['data-choices-limit']) choiceData.maxItemCount = attrs['data-choices-limit'].value.toString();
            if (attrs['data-choices-editItem-true']) choiceData.maxItemCount = true;
            if (attrs['data-choices-editItem-false']) choiceData.maxItemCount = false;
            if (attrs['data-choices-text-unique-true']) {
                choiceData.duplicateItemsAllowed = false;
                choiceData.paste = false;
            }
            if (attrs['data-choices-text-disabled-true']) choiceData.addItems = false;

            attrs['data-choices-text-disabled-true']
                ? new Choices(item, choiceData).disable()
                : new Choices(item, choiceData);
        });
    });
});
