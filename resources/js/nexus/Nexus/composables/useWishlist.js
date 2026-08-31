import { ref, computed } from 'vue';

const STORAGE_KEY = 'wishlist';

const items = ref([]);

const isAuthed = typeof document !== 'undefined'
    && !!document.querySelector('meta[name="user-id"]');

const loadFromStorage = () => {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        items.value = Array.isArray(parsed) ? parsed : [];
    } catch {
        items.value = [];
    }
};

const saveToStorage = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value));
};

const loadFromServer = async () => {
    try {
        const { data } = await window.axios.get('/api/wishlist');
        items.value = Array.isArray(data?.data) ? data.data : [];
    } catch (e) {
        console.error('Failed to load wishlist from server:', e);
        items.value = [];
    }
    saveToStorage();
};

const syncGuestToServer = async () => {
    let guestItems = [];
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        guestItems = Array.isArray(parsed) ? parsed : [];
    } catch {
        guestItems = [];
    }

    if (guestItems.length === 0) return;

    try {
        await window.axios.post('/api/wishlist/sync', { items: guestItems });
    } catch (e) {
        console.error('Failed to sync guest wishlist:', e);
    }
};

const has = (type, id) =>
    items.value.some(i => i.type === type && String(i.id) === String(id));

const add = async (type, id) => {
    if (has(type, id)) return;

    items.value.push({ type, id });

    if (isAuthed) {
        try {
            await window.axios.post('/api/wishlist/add', { type, id });
        } catch (e) {
            items.value = items.value.filter(
                i => !(i.type === type && String(i.id) === String(id))
            );
            console.error('Failed to add to wishlist:', e);
        }
    }

    saveToStorage();
};

const remove = async (type, id) => {
    if (!has(type, id)) return;

    const removed = items.value.filter(
        i => i.type === type && String(i.id) === String(id)
    );
    items.value = items.value.filter(
        i => !(i.type === type && String(i.id) === String(id))
    );

    if (isAuthed) {
        try {
            await window.axios.post('/api/wishlist/remove', { type, id });
        } catch (e) {
            items.value.push(...removed);
            console.error('Failed to remove from wishlist:', e);
        }
    }

    saveToStorage();
};

const toggle = (type, id) => {
    has(type, id) ? remove(type, id) : add(type, id);
};

const loadDetails = async () => {
    if (isAuthed) {
        try {
            const { data } = await window.axios.get('/api/wishlist/details');
            return Array.isArray(data?.data) ? data.data : [];
        } catch (e) {
            console.error('Failed to load wishlist details:', e);
            return [];
        }
    }

    let guestItems = [];
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        guestItems = Array.isArray(parsed) ? parsed : [];
    } catch {
        guestItems = [];
    }

    if (guestItems.length === 0) return [];

    try {
        const { data } = await window.axios.post('/api/wishlist/resolve', { items: guestItems });
        return Array.isArray(data?.data) ? data.data : [];
    } catch (e) {
        console.error('Failed to resolve wishlist:', e);
        return [];
    }
};

const count = computed(() => items.value.length);

if (isAuthed) {
    (async () => {
        await syncGuestToServer();
        await loadFromServer();
    })();
} else {
    loadFromStorage();
}

if (typeof window !== 'undefined' && !isAuthed) {
    window.addEventListener('storage', (e) => {
        if (e.key === STORAGE_KEY) {
            loadFromStorage();
        }
    });
}

export function useWishlist() {
    return {
        items,
        count,
        isAuthed,
        has,
        add,
        remove,
        toggle,
        loadDetails,
    };
}
