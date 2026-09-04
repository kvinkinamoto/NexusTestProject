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

    /**
     * #[Field(type: 'text', editor: true)] / #[Field(type: 'editor')] —
     * CKEditor on the textarea inside this component's root (text.blade.php).
     * That root carries wire:ignore, so Livewire's morph never touches
     * anything under it — init() below runs exactly once, on first mount,
     * instead of needing to destroy and recreate the editor on every single
     * render the way a global [data-ckeditor] querySelectorAll scan (this
     * project's previous approach, and Choices.js's before that — see
     * .ai/rules/views.md) had to.
     *
     * modelPath is the Livewire property this editor's content syncs to
     * (e.g. 'data.description') — passed in from the Blade attribute rather
     * than read off a wire:model on the textarea, since wire:ignore means
     * Livewire itself never binds to anything in here.
     */
    Alpine.data('nexusCkEditor', (modelPath) => ({
        editor: null,

        init() {
            if (typeof CKEDITOR === 'undefined') {
                return;
            }

            this.editor = CKEDITOR.replace(this.$el.querySelector('textarea'), {
                filebrowserImageBrowseUrl: '/elfinder/ckeditor',
                allowedContent: true,
                // CKEditor auto-measures the source <textarea>'s width at
                // replace() time; inside a CSS grid cell it sometimes reads 0
                // before the grid has laid out, collapsing the whole
                // toolbar. An explicit width sidesteps that measurement
                // entirely.
                width: '100%',
            });

            const syncToLivewire = () => this.$wire.set(modelPath, this.editor.getData(), false);

            // 'change' is CKEditor 4's own content-change event, but in
            // practice it doesn't reliably fire while typing in this
            // build/config — 'blur' (the user tabbing/clicking away, which
            // also happens right before they hit Save) is what actually,
            // consistently fires, so that's the sync this depends on;
            // 'change' stays wired too as a harmless best-effort extra.
            this.editor.on('change', syncToLivewire);
            this.editor.on('blur', syncToLivewire);
        },
    }));
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
 * #[Field(type: 'images'|'videos')] gallery pickers (field_types/images.blade.php,
 * videos.blade.php) — each has one static hidden <input data-multi-field="{name}">
 * that the shared elFinder popup (processSelectedFile() in layouts/adminpanel.blade.php
 * and layouts/blank.blade.php) writes a chosen path into, firing a native
 * 'input' event. Delegated (not bound per-element) so it survives Livewire
 * re-rendering that input. Calls ManagesMultiFileFields::addMultiFileValue() directly
 * — an array of unknown/changing length has no single element a plain
 * wire:model could bind to.
 */
document.addEventListener('input', (e) => {
    const el = e.target;
    const fieldName = el.dataset && el.dataset.multiField;
    if (!fieldName || !el.value) return;

    const root = el.closest('[wire\\:id]');
    if (!root) return;

    window.Livewire?.find(root.getAttribute('wire:id'))?.call('addMultiFileValue', fieldName, el.value);
    el.value = '';
});

/**
 * Neither CKEditor nor Choices.js need a [data-*] querySelectorAll scan or a
 * Livewire.hook('morphed', ...) re-init here anymore. CKEditor moved to a
 * wire:ignore + Alpine.data('nexusCkEditor', ...) component (see the
 * alpine:init listener above) that initializes once and is never touched by
 * morph again. Every Livewire-rendered field type that used to carry
 * data-choices (select, enum, gender, wishlistable_type_field,
 * custom_delivery_type, custom_delivery_method, custom_payment_method) now
 * renders its own plain Alpine dropdown instead (see
 * livewire/field_types/_native_select.blade.php) — Choices' morph-survival
 * needs (wrapper stripped on every update, stale listeners orphaned on the
 * pre-morph element, the picked value only reflected via the `selected`
 * HTML attribute at (re)init time) had turned into a standing source of
 * "reverts to placeholder after a pick" bugs, five of those seven field
 * types still had that bug live before this rewrite (see .ai/rules/views.md
 * for the sibling rule this class of bug prompted).
 *
 * [data-choices] remains for the legacy (non-Livewire, full-page-reload)
 * pipeline's own field_types/*.blade.php partials, which have no morph
 * cycle to survive and so don't need any of this — their own
 * DOMContentLoaded init in layouts/adminpanel.blade.php and
 * layouts/blank.blade.php still handles them.
 */
