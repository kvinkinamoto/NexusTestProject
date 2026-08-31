<script setup>
import { computed } from 'vue';
import { useWishlist } from '@/nexus/composables/useWishlist';

const props = defineProps({
    productId: { type: [Number, String], required: true },
    type: { type: String, default: 'product' },
});

const { has, toggle } = useWishlist();

const isAdded = computed(() => has(props.type, props.productId));

const onClick = () => toggle(props.type, props.productId);
</script>

<template>
    <a href="#"
       class="text-primary d-flex align-items-center justify-content-center me-0 wishlist-btn"
       :aria-pressed="isAdded"
       @click.prevent="onClick">
        <span class="rounded-circle btn-sm-square border">
            <i :class="isAdded ? 'fas fa-heart' : 'far fa-heart'"></i>
        </span>
    </a>
</template>

<style scoped lang="scss">
.wishlist-btn {
    transition: transform 0.15s ease;

    &:hover {
        transform: scale(1.1);
    }
}
</style>
