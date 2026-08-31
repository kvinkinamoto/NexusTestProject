<script setup>
import { computed } from 'vue';
import { useCart } from '@/nexus/composables/useCart';

const { products, totalAmount } = useCart();

const isEmpty = computed(() => products.value.length === 0);
const total = computed(() => Number(totalAmount.value || 0).toFixed(2));
</script>

<template>
    <div v-if="!isEmpty" class="row g-4 justify-content-end mt-1">
        <div class="col-8"></div>
        <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
            <div class="bg-light rounded">
                <div class="p-4">
                    <h1 class="display-6 mb-0">
                        {{ $t('cartTotalTitle') }} <span class="fw-normal">{{ $t('cartTotalLabel') }}</span>
                    </h1>
                </div>
                <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between">
                    <h5 class="mb-0 ps-4 me-4">{{ $t('cartTotal') }}</h5>
                    <p class="mb-0 pe-4">{{ $formatPrice(totalAmount) }}</p>
                </div>
                <button class="btn btn-primary rounded-pill px-4 py-3 text-uppercase mb-4 ms-4"
                        type="button"
                        disabled>
                    {{ $t('cartCheckout') }}
                </button>
            </div>
        </div>
    </div>
</template>
