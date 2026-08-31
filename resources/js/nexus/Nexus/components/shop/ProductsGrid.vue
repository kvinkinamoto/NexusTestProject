<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    apiUrl:         { type: String,  required: true },
    type:           { type: String,  required: true },  // 'category' | 'search'
    context:        { type: Object,  default: () => ({}) },
    labelFound:     { type: String,  default: 'Found:' },
    labelEmpty:     { type: String,  default: 'No products found.' },
    labelAddToCart: { type: String,  default: 'Add to Cart' },
});

const loading  = ref(true);
const products = ref([]);
const total    = ref(0);
const currPage = ref(1);
const lastPage = ref(1);

const delays = ['0.1s', '0.3s', '0.5s', '0.7s'];

async function fetchProducts() {
    loading.value = true;
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const params    = new URLSearchParams({
            type:      props.type,
            f:         urlParams.get('f')         ?? '',
            price_min: urlParams.get('price_min') ?? '',
            price_max: urlParams.get('price_max') ?? '',
            sort:      urlParams.get('sort')       ?? 'default',
            page:      urlParams.get('page')       ?? '1',
            ...props.context,
        });

        const res  = await fetch(`${props.apiUrl}?${params}`);
        const json = await res.json();

        products.value = json.data   ?? [];
        total.value    = json.total  ?? 0;
        currPage.value = json.current_page ?? 1;
        lastPage.value = json.last_page    ?? 1;
    } catch (e) {
        products.value = [];
    } finally {
        loading.value = false;
    }
}

function pageUrl(page) {
    const p = new URLSearchParams(window.location.search);
    p.set('page', page);
    return window.location.pathname + '?' + p.toString();
}

function tagStyle(tag) {
    return [
        tag.bg_color   ? `background:${tag.bg_color}`   : '',
        tag.text_color ? `color:${tag.text_color}` : '',
    ].filter(Boolean).join(';');
}

const pageNumbers = computed(() => {
    const pages = [];
    for (let i = 1; i <= lastPage.value; i++) pages.push(i);
    return pages;
});

onMounted(fetchProducts);
</script>

<template>
    <div>
        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
        </div>

        <template v-else>
            <p v-if="total > 0" class="text-muted mb-3">{{ labelFound }} {{ total }}</p>

            <div v-if="products.length === 0" class="alert alert-info">
                {{ labelEmpty }}
            </div>

            <template v-else>
                <div class="row g-4">
                    <div
                        v-for="(product, index) in products"
                        :key="product.id"
                        class="col-md-6 col-lg-4 col-xl-3"
                    >
                        <div class="product-item rounded wow fadeInUp" :data-wow-delay="delays[index % 4]">
                            <div class="product-item-inner border rounded">
                                <div class="product-item-inner-item">
                                    <img
                                        v-if="product.image"
                                        :src="product.image"
                                        :alt="product.name"
                                        class="img-fluid w-100 rounded-top"
                                    >
                                    <div v-else class="bg-light rounded-top w-100" style="height:220px;"></div>

                                    <div
                                        v-if="product.tag"
                                        :class="'product-' + product.tag.key"
                                        :style="tagStyle(product.tag)"
                                    >{{ product.tag.name }}</div>

                                    <div class="product-details">
                                        <a href="#"><i class="fa fa-eye fa-1x"></i></a>
                                    </div>
                                </div>

                                <div class="text-center rounded-bottom p-4">
                                    <a href="#" class="d-block mb-2">{{ product.category_name }}</a>
                                    <a href="#" class="d-block h4 product-card-name" :title="product.name">
                                        {{ product.name }}
                                    </a>
                                    <template v-if="product.price_discount && Number(product.price_discount) > 0">
                                        <del class="me-2 fs-5">{{ $formatPrice(product.price) }}</del>
                                        <span class="text-primary fs-5">{{ $formatPrice(product.price_discount) }}</span>
                                    </template>
                                    <span v-else class="text-primary fs-5">{{ $formatPrice(product.price) }}</span>
                                </div>
                            </div>

                            <div class="product-item-add border border-top-0 rounded-bottom text-center p-4 pt-0">
                                <a href="#" class="btn btn-primary border-secondary rounded-pill py-2 px-4 mb-4">
                                    <i class="fas fa-shopping-cart me-2"></i> {{ labelAddToCart }}
                                </a>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="d-flex">
                                        <a href="#" class="text-primary d-flex align-items-center justify-content-center me-3">
                                            <span class="rounded-circle btn-sm-square border">
                                                <i class="fas fa-random"></i>
                                            </span>
                                        </a>
                                        <wishlist-button-add :product-id="product.id"></wishlist-button-add>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="lastPage > 1" class="pagination d-flex justify-content-center mt-5">
                    <a :href="currPage > 1 ? pageUrl(currPage - 1) : '#'" class="rounded">&laquo;</a>
                    <a
                        v-for="page in pageNumbers"
                        :key="page"
                        :href="pageUrl(page)"
                        :class="{ active: page === currPage }"
                        class="rounded"
                    >{{ page }}</a>
                    <a :href="currPage < lastPage ? pageUrl(currPage + 1) : '#'" class="rounded">&raquo;</a>
                </div>
            </template>
        </template>
    </div>
</template>
