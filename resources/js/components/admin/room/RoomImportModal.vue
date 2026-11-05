<script setup lang="ts">
import { ImportSummary } from '@/components/shared/import';
import { InputFile } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Progress } from '@/components/ui/progress';
import { useImportSummary } from '@/composables/useImportSummary';
import { importMethod } from '@/routes/admin/management/rooms';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ show: boolean }>();

const emit = defineEmits<{ (e: 'close'): void }>();

const { summary } = useImportSummary();

// disable the button if there is no file inputted and it is processing.
const isBlocked = computed(() => !form.isDirty || form.processing);

const form = useForm<{ file: File | null }>({ file: null });

const handleSubmit = () => {
    form.post(importMethod.url(), {
        onError: () => {
            form.reset();
        },
        onSuccess: () => {
            form.reset();
            form.clearErrors();
        },
    });
};

const onClose = () => {
    form.reset();
    form.clearErrors();
    summary.value = null;
    emit('close');
};

const isValid = (s: any) => !!s && Number.isFinite(+s.processed) && Number.isFinite(+s.successful) && Number.isFinite(+s.failed);
</script>

<template>
    <ModalForm
        :open="props.show"
        :isBlocked="isBlocked"
        :closeOnOverlayClick="!form.processing"
        title="modal.action.import_room"
        confirmLabel="action.import"
        icon="lucide:import"
        @close="onClose"
        @submit="handleSubmit"
    >
        <form @submit.prevent="handleSubmit">
            <div class="space-y-2">
                <InputFile v-model="form.file" :error="form.errors.file" accept=".xlsx,.xls,.csv" />
                <Progress
                    v-if="form.progress"
                    :model-value="
                        Number.isFinite(Number(form.progress?.percentage)) ? Math.max(0, Math.min(100, Number(form.progress!.percentage))) : null
                    "
                />
                <ImportSummary v-if="isValid(summary)" :summary="summary" class="mb-4" />
            </div>
        </form>
    </ModalForm>
</template>
