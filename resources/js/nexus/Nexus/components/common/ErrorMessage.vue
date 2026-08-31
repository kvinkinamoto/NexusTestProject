<script setup>
defineProps({
    message: { type: String, default: '' },
});
</script>

<template>
    <span v-if="message" class="error">{{ message }}</span>
</template>

<style scoped lang="scss">
.error {
    color: #dc3545;
    display: block;
    font-size: 0.875rem;
    line-height: 1.3;
}
</style>
