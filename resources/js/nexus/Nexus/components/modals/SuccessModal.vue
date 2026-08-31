<script setup>
import BaseModal from '@/nexus/Components/modals/BaseModal.vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, required: true },
    description: { type: String, default: '' },
    buttonLabel: { type: String, required: true },
});

const emit = defineEmits(['close']);

const onClose = () => emit('close');
</script>

<template>
    <BaseModal :model-value="props.modelValue" modal-class="success-modal" @close="onClose">
        <div class="success-modal__dialog">
            <button type="button"
                    class="success-modal__close"
                    aria-label="Close"
                    @click="onClose">
                <i class="fas fa-times"></i>
            </button>

            <div class="success-modal__icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h3 class="success-modal__title">{{ title }}</h3>
            <p v-if="description" class="success-modal__description">{{ description }}</p>

            <button type="button" class="btn btn-primary rounded-pill px-4 py-2" @click="onClose">
                {{ buttonLabel }}
            </button>
        </div>
    </BaseModal>
</template>

<style scoped lang="scss">
.success-modal__dialog {
    position: relative;
    background: #fff;
    border-radius: 1rem;
    max-width: 480px;
    width: 100%;
    padding: 2.5rem 2rem 2rem;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
}

.success-modal__close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 2rem;
    height: 2rem;
    background: transparent;
    border: 0;
    font-size: 1rem;
    color: #6c757d;
    cursor: pointer;

    &:hover {
        color: #212529;
    }
}

.success-modal__icon {
    color: #198754;
    font-size: 3.5rem;
    line-height: 1;
    margin-bottom: 1rem;
}

.success-modal__title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
}

.success-modal__description {
    color: #6c757d;
    margin-bottom: 1.5rem;
}
</style>
