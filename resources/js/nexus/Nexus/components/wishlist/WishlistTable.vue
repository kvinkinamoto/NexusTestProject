<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useWishlist } from '@/nexus/composables/useWishlist';

const wishlist = useWishlist();
const { items, remove } = wishlist;

const details = ref([]);
const loading = ref(true);

const refresh = async () => {
    loading.value = true;
    details.value = await wishlist.loadDetails();
    loading.value = false;
};

onMounted(refresh);

watch(items, () => {
    const valid = new Set(items.value.map(i => `${i.type}:${i.id}`));
    details.value = details.value.filter(
        d => valid.has(`${d.type}:${d.id}`)
    );
}, { deep: true });

const isEmpty = computed(() => items.value.length === 0);

const typeBadgeClasses = {
    product: 'bg-success',
    blog_post: 'bg-info',
    blog_category: 'bg-warning',
};

const typeLabelKeys = {
    product: 'typeProduct',
    blog_post: 'typeBlogPost',
    blog_category: 'typeBlogCategory',
};
</script>

<template>
    <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div v-else-if="isEmpty" class="text-center py-5">
        <p class="mb-0 fs-5 text-muted">{{ $t('wishlistEmpty') }}</p>
    </div>
    <div v-else class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th scope="col">{{ $t('colImage') }}</th>
                    <th scope="col">{{ $t('colName') }}</th>
                    <th scope="col">{{ $t('colType') }}</th>
                    <th scope="col">{{ $t('colPrice') }}</th>
                    <th scope="col">{{ $t('colHandle') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in details" :key="`${item.type}-${item.id}`">
                    <td class="py-3" style="width: 100px;">
                        <img v-if="item.image"
                             :src="item.image"
                             :alt="item.title"
                             class="img-fluid rounded"
                             style="max-height: 60px; object-fit: cover;">
                        <div v-else
                             class="bg-light rounded"
                             style="width: 60px; height: 60px;"></div>
                    </td>
                    <th scope="row">
                        <p class="mb-0 py-3">{{ item.title }}</p>
                    </th>
                    <td>
                        <span class="badge text-white"
                              :class="typeBadgeClasses[item.type] || 'bg-secondary'">
                            {{ $t(typeLabelKeys[item.type] || item.type) }}
                        </span>
                    </td>
                    <td>
                        <p v-if="item.type === 'product'" class="mb-0 py-3">
                            <template v-if="item.original_price && Number(item.original_price) > Number(item.price)">
                                <del class="text-muted me-2">{{ $formatPrice(item.original_price) }}</del>
                                <span class="text-primary">{{ $formatPrice(item.price) }}</span>
                            </template>
                            <template v-else>
                                {{ $formatPrice(item.price) }}
                            </template>
                        </p>
                        <p v-else class="mb-0 py-3 text-muted">—</p>
                    </td>
                    <td class="py-3">
                        <button class="btn btn-md rounded-circle bg-light border"
                                type="button"
                                @click="remove(item.type, item.id)">
                            <i class="fa fa-times text-danger"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
