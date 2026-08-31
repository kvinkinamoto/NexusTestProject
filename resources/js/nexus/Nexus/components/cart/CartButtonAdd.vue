<script setup>
import { computed } from 'vue';
import { useCart } from '@/nexus/composables/useCart';

const props = defineProps({
    productId: { type: [Number, String], required: true },
    label: { type: String, required: true },
    addedLabel: { type: String, required: true },
});

const { has, add, remove } = useCart();

const isAdded = computed(() => has(props.productId));

const onClick = () => {
    isAdded.value ? remove(props.productId) : add(props.productId, 1);
};
</script>

<template>
    <a href="#"
       class="btn btn-primary border-secondary rounded-pill py-2 px-4 cart-add-btn"
       :aria-pressed="isAdded"
       @click.prevent="onClick">
        <i class="fas fa-shopping-cart me-2"></i>
        {{ isAdded ? addedLabel : label }}
    </a>
</template>

<style scoped lang="scss">
.cart-add-btn {
    transition: transform 0.15s ease, background-color 0.15s ease;

    &:hover {
        transform: scale(1.02);
    }
}
</style>
