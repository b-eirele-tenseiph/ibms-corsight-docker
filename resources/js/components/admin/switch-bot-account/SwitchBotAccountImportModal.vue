<script setup lang="ts">
import { ImportSummary } from '@/components/shared/import';
import { InputFile } from '@/components/shared/inputs';
import { LoaderProgress } from '@/components/shared/loaders';
import { ModalForm } from '@/components/shared/modals';
import { useImportSummary } from '@/composables/useImportSummary';
import { importMethod } from '@/routes/admin/management/switchbot/accounts';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const emit = defineEmits(['close']);
const props = defineProps<{ show: boolean }>();

const { summary } = useImportSummary();

const isUploading = ref(false);

// Initialize form
const form = useForm<{ file: File | null }>({
    file: null,
});

// Reference to LoaderProgress component (the GSAP-based fake loader)
const loaderRef = ref<InstanceType<typeof LoaderProgress> | null>(null);

const isSubmitButtonDisabled = computed(() => !form.file || isUploading.value);

function onSubmit() {
    // Client-side validation
    if (!form.file) {
        form.setError('file', 'Please select a file before submitting.');
        return;
    }

    // Clear old errors
    form.clearErrors();

    // Mark uploading as true and start animation
    isUploading.value = true;
    loaderRef.value?.startFakeProgress();

    form.post(importMethod.url(), {
        forceFormData: true,
        onSuccess: () => {
            loaderRef.value?.finishProgress(); // ✅ finish the LoaderProgress animation
            form.reset();
        },
        onError: (errors) => {
            loaderRef.value?.finishProgress(); // ✅ also finish if it fails

            // Convert array to string
            if (errors.file) {
                const message = Array.isArray(errors.file) ? errors.file.join(', ') : errors.file;
                form.setError('file', message);
            }
        },
        onFinish: () => {
            isUploading.value = false;
        },
    });
}

function onClose() {
    form.reset();
    form.clearErrors();
    summary.value = null;
    loaderRef.value?.finishProgress(); // reset LoaderProgress if modal closed
    emit('close');
}

const isValid = (s: any) => !!s && Number.isFinite(+s.processed) && Number.isFinite(+s.successful) && Number.isFinite(+s.failed);
</script>

<template>
    <ModalForm
        :open="props.show"
        title="modal.action.import_switch_bot_account"
        icon="lucide:download"
        confirmLabel="action.import"
        :isBlocked="isSubmitButtonDisabled"
        :isCancelBlocked="isUploading"
        :closeOnOverlayClick="false"
        @submit="onSubmit"
        @close="onClose"
    >
        <div class="space-y-4">
            <InputFile v-model="form.file" :error="form.errors.file" />

            <LoaderProgress ref="loaderRef" />

            <ImportSummary v-if="isValid(summary)" :summary="summary" class="mb-4" />
        </div>
    </ModalForm>
</template>
