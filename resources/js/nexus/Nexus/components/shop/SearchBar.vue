<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    action: { type: String, required: true },
    suggestUrl: { type: String, required: true },
    placeholder: { type: String, default: "Search..." },
    initial: { type: String, default: "" },
    categories: { type: Array, default: () => [] }, // [{name, slug}]
    initialCategory: { type: String, default: "" },
    labelAllCats: { type: String, default: "All Category" },
});

const query = ref(props.initial);
const selectedCat = ref(props.initialCategory);
const suggestions = ref([]);
const open = ref(false);
let timer = null;

watch(query, (val) => {
    clearTimeout(timer);
    if (val.trim().length < 2) {
        suggestions.value = [];
        open.value = false;
        return;
    }
    timer = setTimeout(() => fetchSuggestions(val.trim()), 300);
});

async function fetchSuggestions(q) {
    try {
        const res = await fetch(
            `${props.suggestUrl}?q=${encodeURIComponent(q)}`,
        );
        suggestions.value = await res.json();
        open.value = suggestions.value.length > 0;
    } catch {
        suggestions.value = [];
        open.value = false;
    }
}

function selectItem(item) {
    open.value = false;
    window.location.href = `${props.action}?q=${encodeURIComponent(item.name)}`;
}

function onBlur() {
    setTimeout(() => {
        open.value = false;
    }, 150);
}

function onFocus() {
    if (suggestions.value.length > 0) open.value = true;
}
</script>

<template>
    <form
        method="GET"
        :action="action"
        @submit="open = false"
        class="position-relative"
    >
        <div class="d-flex border rounded-pill">
            <input
                class="form-control border-0 rounded-pill w-100 py-3"
                type="text"
                name="q"
                v-model="query"
                :placeholder="placeholder"
                autocomplete="off"
                @blur="onBlur"
                @focus="onFocus"
            />
            <select
                v-if="categories.length > 0"
                class="form-select text-dark border-0 border-start rounded-0 p-3"
                style="width: 200px"
                name="category"
                v-model="selectedCat"
            >
                <option value="">{{ labelAllCats }}</option>
                <option
                    v-for="cat in categories"
                    :key="cat.slug"
                    :value="cat.slug"
                >
                    {{ cat.name }}
                </option>
            </select>
            <button
                type="submit"
                class="btn btn-primary rounded-pill py-3 px-5"
                style="border: 0"
            >
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div
            v-if="open"
            class="position-absolute bg-white border rounded shadow mt-1"
            style="
                z-index: 1050;
                top: 100%;
                left: 0;
                right: 0;
                max-height: 360px;
                overflow-y: auto;
            "
        >
            <div
                v-for="item in suggestions"
                :key="item.id"
                class="suggest-item d-flex align-items-center gap-3 px-3 py-2 border-bottom"
                @mousedown.prevent="selectItem(item)"
            >
                <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.name"
                    class="rounded flex-shrink-0"
                    style="width: 44px; height: 44px; object-fit: cover"
                />
                <div
                    v-else
                    class="rounded flex-shrink-0 bg-light"
                    style="width: 44px; height: 44px"
                ></div>

                <div class="flex-grow-1 overflow-hidden">
                    <div class="small fw-semibold text-dark text-truncate">
                        {{ item.name }}
                    </div>
                    <div class="small text-primary">{{ $formatPrice(item.price) }}</div>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>
.suggest-item {
    cursor: pointer;
    transition: background 0.15s;
}
.suggest-item:hover {
    background: #f8f9fa;
}
.suggest-item:last-child {
    border-bottom: 0 !important;
}
</style>
