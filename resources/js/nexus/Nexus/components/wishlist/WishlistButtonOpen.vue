<script setup>
import { useWishlist } from '@/nexus/composables/useWishlist';

defineProps({
    href: { type: String, default: '#' },
});

const { count } = useWishlist();
</script>

<template>
    <a :href="href" class="text-muted d-flex align-items-center justify-content-center me-3 wishlist-open">
        <span class="rounded-circle btn-md-square border position-relative">
            <i class="fas fa-heart"></i>
            <span v-if="count > 0" class="wishlist-badge">{{ count }}</span>
        </span>
    </a>
</template>

<style scoped lang="scss">
.wishlist-open {
    transition: transform 0.15s ease;

    &:hover {
        transform: scale(1.05);
    }
}

.wishlist-badge {
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
