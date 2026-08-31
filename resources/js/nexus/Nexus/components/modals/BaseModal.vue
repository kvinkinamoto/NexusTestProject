<script setup>
import { watch } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    modalClass: { type: String, default: '' },
});

const emit = defineEmits(['close']);

watch(
    () => props.modelValue,
    (open) => {
        document.body.style.overflow = open ? 'hidden' : '';
    }
);
</script>

<template>
    <Teleport to="body">
        <Transition>
            <div v-if="modelValue" :class="['modal', modalClass]" role="dialog" aria-modal="true">
                <div class="modal__overlay" @click="emit('close')"></div>
                <div class="modal__content">
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped lang="scss">
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1.25rem 0.75rem;

    &__overlay {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #000;
        opacity: 0.6;
    }

    &__content {
        overflow-y: auto;
        max-height: 100%;
        position: relative;
        z-index: 2;
    }
}

.v-enter-active,
.v-leave-active {
    transition: opacity 0.3s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}

.v-enter-active .modal__content,
.v-leave-active .modal__content {
    transition: transform 0.3s ease;
}

.v-enter-from .modal__content,
.v-leave-to .modal__content {
    transform: translateY(20px);
}
</style>
