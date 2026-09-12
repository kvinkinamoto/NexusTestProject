import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

/**
 * Separate Vite entry, loaded only by resources/views/layouts/storefront.blade.php
 * — never together with Livewire (admin panel), which bundles its own Alpine
 * instance (see resources/js/app.js's top comment on the multi-instance
 * conflict that causes). Starting a fresh Alpine here is therefore safe.
 */
window.Alpine = Alpine;
Alpine.plugin(collapse);

/**
 * fetch() wrapper for the existing Nexus JSON endpoints (/api/cart/*,
 * /api/wishlist/*, /api/orders, the JSON-only auth endpoints) — attaches the
 * CSRF token the `web` middleware group's session guard requires (there is
 * no Sanctum SPA/token setup in this app, just plain session cookies).
 */
window.storefrontFetch = async function storefrontFetch(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token ?? '',
            ...(options.headers ?? {}),
        },
        body: options.body ? JSON.stringify(options.body) : undefined,
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(data.message || 'Помилка запиту');
        error.data = data;
        error.status = response.status;
        throw error;
    }

    return data;
};

/**
 * Drives slider::public.slider's carousel for 2+ slides (see that view and
 * app.css's .nexus-slider* rules) — a flex track shifted by translateX,
 * auto-rotating on a timer that resets on manual prev/next/dot interaction
 * so the slider doesn't jump right after someone clicks through it. Only
 * instantiated when the partial has more than one slide; a single slide
 * renders via the same CSS with no transform and no JS at all.
 */
Alpine.data('nexusSlider', (count) => ({
    active: 0,
    timer: null,
    init() {
        this.restart();
    },
    restart() {
        clearInterval(this.timer);
        this.timer = setInterval(() => this.next(), 5000);
    },
    next() {
        this.active = (this.active + 1) % count;
        this.restart();
    },
    prev() {
        this.active = (this.active - 1 + count) % count;
        this.restart();
    },
    go(index) {
        this.active = index;
        this.restart();
    },
}));

/**
 * Wishlist heart button — used on product cards and the product page.
 * Cart/wishlist both work for guests too (session-scoped row on the
 * backend), so this just calls the API directly regardless of auth state.
 */
Alpine.data('wishlistButton', (productId, initialActive = false) => ({
    active: initialActive,
    busy: false,
    async toggle() {
        this.busy = true;
        try {
            await window.storefrontFetch(this.active ? '/api/wishlist/remove' : '/api/wishlist/add', {
                method: 'POST',
                body: { product_id: productId },
            });
            this.active = !this.active;
            document.dispatchEvent(new CustomEvent('wishlist:updated'));
        } catch (e) {
            alert(e.message);
        } finally {
            this.busy = false;
        }
    },
}));

/** "Add to cart" button — product cards' hover overlay and the product page. */
Alpine.data('addToCartButton', (productId) => ({
    busy: false,
    added: false,
    async add(quantity = 1) {
        this.busy = true;
        try {
            await window.storefrontFetch('/api/cart/add', {
                method: 'POST',
                body: { id: productId, quantity },
            });
            this.added = true;
            document.dispatchEvent(new CustomEvent('cart:updated'));
            setTimeout(() => {
                this.added = false;
            }, 1500);
        } catch (e) {
            alert(e.message);
        } finally {
            this.busy = false;
        }
    },
}));

// Keeps the header cart-count badge in sync after any add/remove/update
// elsewhere on the page, without a full reload.
document.addEventListener('cart:updated', async () => {
    const badge = document.getElementById('header-cart-count');
    try {
        const cart = await window.storefrontFetch('/api/cart/');
        const count = (cart.products ?? []).reduce((sum, p) => sum + p.quantity, 0);
        if (badge) {
            badge.textContent = count;
            badge.hidden = count <= 0;
        }
    } catch (e) {
        // Cart endpoint failing here shouldn't break whatever action just happened.
    }
});

// Same as cart:updated above, for the header wishlist-count badge.
document.addEventListener('wishlist:updated', async () => {
    const badge = document.getElementById('header-wishlist-count');
    try {
        const res = await window.storefrontFetch('/api/wishlist/');
        const count = Array.isArray(res.data) ? res.data.length : 0;
        if (badge) {
            badge.textContent = count;
            badge.hidden = count <= 0;
        }
    } catch (e) {
        // Wishlist endpoint failing here shouldn't break whatever action just happened.
    }
});

/**
 * Catalog filters/sort/pagination without a full page reload (ТЗ
 * requirement). No JSON catalog API exists, so this fetches the same
 * server-rendered catalog page and swaps in just the #catalog-results
 * fragment — simplest way to get real filtering without inventing a
 * parallel JSON endpoint for what the controller already renders.
 */
Alpine.data('catalogFilters', () => ({
    filtersOpen: false,
    async submit() {
        const form = this.$refs.form;
        const params = new URLSearchParams(new FormData(form)).toString();
        await this.load(form.action + (params ? '?' + params : ''));
    },
    async load(url) {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
        const next = doc.getElementById('catalog-results');
        const current = document.getElementById('catalog-results');
        if (next && current) {
            current.innerHTML = next.innerHTML;
            current.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        window.history.pushState({}, '', url);
    },
}));

/**
 * Live search-as-you-type for the header search box(es) — debounced fetch
 * against /api/search/products, rendered as a dropdown under the input.
 * The surrounding <form> is left untouched so Enter/submit still works as a
 * fallback, taking the visitor to the full /catalog?q= results page.
 */
Alpine.data('searchAutocomplete', () => ({
    query: '',
    results: [],
    moreUrl: '',
    open: false,
    busy: false,
    timer: null,
    onInput() {
        clearTimeout(this.timer);
        const q = this.query.trim();
        if (q.length < 2) {
            this.results = [];
            this.open = false;
            return;
        }
        this.timer = setTimeout(() => this.fetchResults(q), 300);
    },
    async fetchResults(q) {
        this.busy = true;
        try {
            const res = await fetch('/api/search/products?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
            const data = await res.json();
            this.results = data.products ?? [];
            this.moreUrl = data.more_url ?? '';
            this.open = true;
        } catch (e) {
            this.results = [];
        } finally {
            this.busy = false;
        }
    },
    formatPrice(value) {
        return new Intl.NumberFormat('uk-UA').format(Math.round(value || 0)) + ' грн';
    },
}));

/** Cart page — loads/mutates the cart entirely against the existing /api/cart/* JSON endpoints. */
Alpine.data('cartPage', () => ({
    loading: true,
    cart: null,
    error: null,
    promoCode: '',
    promoBusy: false,
    async init() {
        await this.refresh();
    },
    async refresh() {
        this.loading = true;
        this.error = null;
        try {
            this.cart = await window.storefrontFetch('/api/cart/');
        } catch (e) {
            this.error = e.message;
        } finally {
            this.loading = false;
        }
    },
    async updateQuantity(id, quantity) {
        if (quantity < 1) {
            return this.remove(id);
        }
        await window.storefrontFetch('/api/cart/update-quantity', { method: 'POST', body: { id, quantity } });
        await this.refresh();
        document.dispatchEvent(new CustomEvent('cart:updated'));
    },
    async remove(id) {
        await window.storefrontFetch('/api/cart/remove', { method: 'POST', body: { id } });
        await this.refresh();
        document.dispatchEvent(new CustomEvent('cart:updated'));
    },
    async applyPromo() {
        if (!this.promoCode) {
            return;
        }
        this.promoBusy = true;
        try {
            await window.storefrontFetch('/api/cart/promo-code/apply', { method: 'POST', body: { code: this.promoCode } });
            await this.refresh();
        } catch (e) {
            alert(e.message);
        } finally {
            this.promoBusy = false;
        }
    },
    async removePromo() {
        await window.storefrontFetch('/api/cart/promo-code/remove', { method: 'POST' });
        await this.refresh();
    },
    formatPrice(value) {
        return new Intl.NumberFormat('uk-UA').format(Math.round(value || 0)) + ' грн';
    },
}));

/**
 * Checkout form — POST /api/orders is guest-friendly on the backend, but
 * since adding to a cart requires login (no guest cart exists anywhere),
 * only logged-in users ever reach this page with anything to check out. For
 * a logged-in user OrderService reads their persisted Cart server-side and
 * ignores any posted `products` array, so this only needs to send the
 * contact/delivery/payment fields.
 */
/**
 * Carriers with a synced address classifier (see
 * App\Nexus\Modules\Delivery\Services\DeliveryAddressSyncService) — the
 * city/warehouse fields below become an autocomplete picker against the
 * cached delivery_cities/delivery_warehouses tables for these; any other
 * delivery key (e.g. courier) keeps the plain free-text city/address inputs.
 */
const DELIVERY_KEYS_WITH_ADDRESS_PICKER = ['nova-poshta', 'ukr-poshta'];

Alpine.data('checkoutForm', (defaultDelivery) => ({
    busy: false,
    selectedDelivery: defaultDelivery,
    another: false,
    cityText: '',
    cityResults: [],
    cityOpen: false,
    cityTimer: null,
    selectedCityId: null,
    addressText: '',
    addressResults: [],
    addressOpen: false,
    addressTimer: null,
    get usesAddressPicker() {
        return DELIVERY_KEYS_WITH_ADDRESS_PICKER.includes(this.selectedDelivery);
    },
    onCityInput() {
        this.selectedCityId = null;
        clearTimeout(this.cityTimer);
        const q = this.cityText.trim();
        if (!this.usesAddressPicker || q.length < 2) {
            this.cityResults = [];
            this.cityOpen = false;
            return;
        }
        this.cityTimer = setTimeout(async () => {
            const res = await fetch('/api/delivery/' + encodeURIComponent(this.selectedDelivery) + '/cities?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
            const data = await res.json();
            this.cityResults = data.cities ?? [];
            this.cityOpen = this.cityResults.length > 0;
        }, 300);
    },
    selectCity(city) {
        this.cityText = city.name;
        this.selectedCityId = city.id;
        this.cityOpen = false;
        this.addressText = '';
        this.fetchWarehouses('');
    },
    onAddressInput() {
        clearTimeout(this.addressTimer);
        if (!this.usesAddressPicker || !this.selectedCityId) {
            this.addressResults = [];
            this.addressOpen = false;
            return;
        }
        this.addressTimer = setTimeout(() => this.fetchWarehouses(this.addressText.trim()), 300);
    },
    async fetchWarehouses(q) {
        const res = await fetch('/api/delivery/cities/' + this.selectedCityId + '/warehouses?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
        const data = await res.json();
        this.addressResults = data.warehouses ?? [];
        this.addressOpen = this.addressResults.length > 0;
    },
    selectWarehouse(warehouse) {
        this.addressText = warehouse.name;
        this.addressOpen = false;
    },
    async submit(el) {
        this.busy = true;
        try {
            const formData = new FormData(el);
            const payload = Object.fromEntries(formData.entries());
            payload.needs_phone_call = formData.has('needs_phone_call') ? 1 : 0;
            payload.is_another_recipient = this.another ? 1 : 0;
            const res = await window.storefrontFetch('/api/orders', { method: 'POST', body: payload });
            const selectedPayment = el.querySelector('input[name="payment_method"]:checked');
            if (selectedPayment?.dataset.type === 'gateway') {
                window.location = '/payment/' + res.order.id;
            } else {
                window.location = '/checkout/thank-you?order=' + res.order.id;
            }
        } catch (e) {
            alert(e.message);
        } finally {
            this.busy = false;
        }
    },
}));

Alpine.start();
