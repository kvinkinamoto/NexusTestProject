import { ref, computed } from 'vue';

const STORAGE_KEY = 'cart';

const products = ref([]);
const totalAmount = ref(0);

const isAuthed = typeof document !== 'undefined'
    && !!document.querySelector('meta[name="user-id"]');

const loadFromStorage = () => {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        products.value = Array.isArray(parsed) ? parsed.filter(i => i && i.id) : [];
    } catch {
        products.value = [];
    }
};

const saveToStorage = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(products.value));
};

const applySummary = (data) => {
    products.value = Array.isArray(data?.products) ? data.products : [];
    totalAmount.value = Number(data?.total_price ?? 0);
    saveToStorage();
};

const refreshSummary = async () => {
    if (isAuthed) {
        try {
            const { data } = await window.axios.get('/api/cart/summary');
            applySummary(data);
        } catch (e) {
            console.error('Failed to load cart summary:', e);
        }
        return;
    }

    if (products.value.length === 0) {
        totalAmount.value = 0;
        return;
    }

    try {
        const { data } = await window.axios.post('/api/cart/summary/resolve', { products: products.value });
        applySummary(data);
    } catch (e) {
        console.error('Failed to resolve cart summary:', e);
    }
};

const syncGuestToServer = async () => {
    let guestProducts = [];
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        guestProducts = Array.isArray(parsed) ? parsed : [];
    } catch {
        guestProducts = [];
    }

    if (guestProducts.length === 0) return;

    try {
        await window.axios.post('/api/cart/sync', { products: guestProducts });
    } catch (e) {
        console.error('Failed to sync guest cart:', e);
    }
};

const findIndex = (id) => products.value.findIndex(i => String(i.id) === String(id));

const has = (id) => findIndex(id) !== -1;

const getQuantity = (id) => {
    const idx = findIndex(id);
    return idx === -1 ? 0 : (products.value[idx].quantity ?? 0);
};

const add = async (id, quantity = 1) => {
    const qty = Math.max(1, parseInt(quantity, 10) || 1);
    const idx = findIndex(id);
    const prev = idx === -1 ? null : { ...products.value[idx] };

    if (idx === -1) {
        products.value.push({ id, quantity: qty });
    } else {
        products.value[idx] = { ...products.value[idx], quantity: (products.value[idx].quantity ?? 0) + qty };
    }

    if (isAuthed) {
        try {
            await window.axios.post('/api/cart/add', { id, quantity: qty });
        } catch (e) {
            if (prev) {
                products.value[idx] = prev;
            } else {
                products.value = products.value.filter(i => String(i.id) !== String(id));
            }
            console.error('Failed to add to cart:', e);
        }
    }

    saveToStorage();
    await refreshSummary();
};

const updateQuantity = async (id, quantity) => {
    const qty = parseInt(quantity, 10);
    const idx = findIndex(id);
    if (idx === -1) return;

    const prev = { ...products.value[idx] };

    if (!isFinite(qty) || qty <= 0) {
        return remove(id);
    }

    products.value[idx] = { ...products.value[idx], quantity: qty };

    if (isAuthed) {
        try {
            await window.axios.post('/api/cart/update-quantity', { id, quantity: qty });
        } catch (e) {
            products.value[idx] = prev;
            console.error('Failed to update cart quantity:', e);
        }
    }

    saveToStorage();
    await refreshSummary();
};

const remove = async (id) => {
    const idx = findIndex(id);
    if (idx === -1) return;

    const removed = products.value[idx];
    products.value = products.value.filter(i => String(i.id) !== String(id));

    if (isAuthed) {
        try {
            await window.axios.post('/api/cart/remove', { id });
        } catch (e) {
            products.value.push(removed);
            console.error('Failed to remove from cart:', e);
        }
    }

    saveToStorage();
    await refreshSummary();
};

const loadDetails = async () => {
    if (isAuthed) {
        try {
            const { data } = await window.axios.get('/api/cart');
            totalAmount.value = Number(data?.total_price ?? 0);
            return Array.isArray(data?.products) ? data.products : [];
        } catch (e) {
            console.error('Failed to load cart details:', e);
            return [];
        }
    }

    if (products.value.length === 0) return [];

    try {
        const { data } = await window.axios.post('/api/cart/resolve', { products: products.value });
        totalAmount.value = Number(data?.total_price ?? 0);
        return Array.isArray(data?.products) ? data.products : [];
    } catch (e) {
        console.error('Failed to resolve cart:', e);
        return [];
    }
};

const count = computed(() => products.value.length);
const totalQuantity = computed(() =>
    products.value.reduce((sum, i) => sum + (parseInt(i.quantity, 10) || 0), 0)
);

if (isAuthed) {
    (async () => {
        await syncGuestToServer();
        await refreshSummary();
    })();
} else {
    loadFromStorage();
    refreshSummary();
}

if (typeof window !== 'undefined' && !isAuthed) {
    window.addEventListener('storage', (e) => {
        if (e.key === STORAGE_KEY) {
            loadFromStorage();
            refreshSummary();
        }
    });
}

export function useCart() {
    return {
        products,
        count,
        totalQuantity,
        totalAmount,
        isAuthed,
        has,
        getQuantity,
        add,
        updateQuantity,
        remove,
        loadDetails,
        refreshSummary,
    };
}
