<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCart } from '@/nexus/composables/useCart';

const cart = useCart();
const { products, updateQuantity, remove } = cart;

const details = ref([]);
const loading = ref(true);

const refresh = async () => {
    loading.value = true;
    details.value = await cart.loadDetails();
    loading.value = false;
};

onMounted(refresh);

const visibleDetails = computed(() =>
    details.value.filter(d => products.value.some(p => String(p.id) === String(d.id)))
);

const isEmpty = computed(() => products.value.length === 0);

const findCartProduct = (id) => products.value.find(p => String(p.id) === String(id));
const findDetail = (id) => details.value.find(d => String(d.id) === String(id));

const currentQuantity = (id) => findCartProduct(id)?.quantity ?? 1;

const onQuantityChange = (id, value) => {
    const qty = parseInt(value, 10);
    if (!isFinite(qty) || qty < 1) return updateQuantity(id, 1);
    const detail = findDetail(id);
    if (detail?.stock != null && qty > detail.stock) {
        return updateQuantity(id, detail.stock);
    }
    updateQuantity(id, qty);
};

const inc = (id) => {
    const next = currentQuantity(id) + 1;
    const detail = findDetail(id);
    if (detail?.stock != null && next > detail.stock) return;
    updateQuantity(id, next);
};

const dec = (id) => {
    const next = currentQuantity(id) - 1;
    if (next < 1) return;
    updateQuantity(id, next);
};

const lineTotal = (item) => (Number(item.price) * currentQuantity(item.id)).toFixed(2);

const atMaxStock = (id) => {
    const detail = findDetail(id);
    return detail?.stock != null && currentQuantity(id) >= detail.stock;
};
</script>

<template>
    <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div v-else-if="isEmpty" class="text-center py-5">
        <p class="mb-0 fs-5 text-muted">{{ $t('cartEmpty') }}</p>
    </div>
    <div v-else class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">{{ $t('colImage') }}</th>
                    <th scope="col">{{ $t('colName') }}</th>
                    <th scope="col">{{ $t('colPrice') }}</th>
                    <th scope="col">{{ $t('colQuantity') }}</th>
                    <th scope="col">{{ $t('colTotal') }}</th>
                    <th scope="col">{{ $t('colHandle') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in visibleDetails" :key="item.id">
                    <td class="py-4" style="width: 100px;">
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
                        <p class="mb-0 py-4">{{ item.title }}</p>
                    </th>
                    <td>
                        <p class="mb-0 py-4">
                            <template v-if="item.original_price && Number(item.original_price) > Number(item.price)">
                                <del class="text-muted me-2">{{ $formatPrice(item.original_price) }}</del>
                                <span class="text-primary">{{ $formatPrice(item.price) }}</span>
                            </template>
                            <template v-else>
                                {{ $formatPrice(item.price) }}
                            </template>
                        </p>
                    </td>
                    <td>
                        <div class="input-group quantity py-4" style="width: 110px;">
                            <div class="input-group-btn">
                                <button type="button"
                                        class="btn btn-sm btn-minus rounded-circle bg-light border"
                                        :disabled="currentQuantity(item.id) <= 1"
                                        @click="dec(item.id)">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <input type="number"
                                   class="form-control form-control-sm text-center border-0"
                                   :value="currentQuantity(item.id)"
                                   min="1"
                                   @change="onQuantityChange(item.id, $event.target.value)">
                            <div class="input-group-btn">
                                <button type="button"
                                        class="btn btn-sm btn-plus rounded-circle bg-light border"
                                        :disabled="atMaxStock(item.id)"
                                        @click="inc(item.id)">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="mb-0 py-4">{{ $formatPrice(lineTotal(item)) }}</p>
                    </td>
                    <td class="py-4">
                        <button type="button"
                                class="btn btn-md rounded-circle bg-light border"
                                @click="remove(item.id)">
                            <i class="fa fa-times text-danger"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
