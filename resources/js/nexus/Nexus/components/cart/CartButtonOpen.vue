<script setup>
import { computed } from 'vue';
import { useCart } from '@/nexus/composables/useCart';

defineProps({
    href: { type: String, default: '#' },
});

const { totalQuantity, totalAmount } = useCart();

const formattedTotal = computed(() => Number(totalAmount.value || 0).toFixed(2));
</script>

<template>
    <a :href="href" class="text-muted d-flex align-items-center justify-content-center cart-open">
        <span class="rounded-circle btn-md-square border position-relative">
            <i class="fas fa-shopping-cart"></i>
            <span v-if="totalQuantity > 0" class="cart-badge">{{ totalQuantity }}</span>
        </span>
        <span v-if="totalQuantity > 0" class="text-dark ms-2">{{ $formatPrice(totalAmount) }}</span>
    </a>
</template>

<style scoped lang="scss">
.cart-open {
    transition: transform 0.15s ease;

    &:hover {
        transform: scale(1.05);
    }
}

.cart-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    background: #dc3545;
    color: #fff;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 600;
    line-height: 18px;
    text-align: center;
}
</style>
