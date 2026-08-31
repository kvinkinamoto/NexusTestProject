<script setup>
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import ErrorMessage from '@/nexus/Components/common/ErrorMessage.vue';
import SuccessModal from '@/nexus/Components/modals/SuccessModal.vue';

const props = defineProps({
    actionUrl: { type: String, default: '/api/subscriber/subscribe' },
});

const form = ref({
    email: '',
});

const errors = ref({
    email: '',
});

const loading = ref(false);
const showSuccessModal = ref(false);

const submit = async () => {
    if (loading.value) return;

    if (!validateForm()) {
        return;
    }

    await storeData();
};

const storeData = async () => {
    loading.value = true;

    try {
        const { data } = await window.axios.post(props.actionUrl, form.value);

        if (data?.success) {
            form.value.email = '';
            showSuccessModal.value = true;
        } else {
            errors.value.email = trans('subscribeFailed');
        }
    } catch (e) {
        const apiErrors = e.response?.data?.errors;

        if (apiErrors?.email?.length) {
            errors.value.email = apiErrors.email[0];
        } else if (e.response?.data?.message) {
            errors.value.email = e.response.data.message;
        } else {
            errors.value.email = trans('subscribeFailed');
        }
    } finally {
        loading.value = false;
    }
};

const validateForm = () => {
    let valid = true;

    if (!form.value.email.trim()) {
        errors.value.email = trans('subscribeRequired');
        valid = false;
    } else {
        errors.value.email = '';
    }

    return valid;
};
</script>

<template>
    <form @submit.prevent="submit" novalidate>
        <div class="position-relative mx-auto rounded-pill">
            <input
                v-model="form.email"
                type="email"
                name="email"
                class="form-control rounded-pill w-100 py-3 ps-4 pe-5"
                :placeholder="$t('subscribePlaceholder')"
                :disabled="loading"
            />
            <button
                type="submit"
                class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 mt-2 me-2"
                :disabled="loading"
            >
                {{ $t('subscribeButton') }}
            </button>
        </div>

        <ErrorMessage :message="errors.email" class="mt-2 ps-3" />
    </form>

    <SuccessModal
        :model-value="showSuccessModal"
        :title="$t('subscribeSuccessTitle')"
        :description="$t('subscribeSuccessDescription')"
        :button-label="$t('subscribeSuccessButton')"
        @close="showSuccessModal = false"
    />
</template>
