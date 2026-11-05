<script setup lang="ts">
import { InputDateAndDate, InputSingleImageFile, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/staff/promo_management';
import { Promo } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

// Emit 'close' event to parent when modal closes or user is updated
const emit = defineEmits(['close']);

const props = defineProps<{
    open: boolean;
    promo: Promo | null;
}>();

const form = useForm({
    id: '',
    title: '',
    description: '',
    valid_start_date: '',
    valid_end_date: '',
    photo_file: null as File | null,
    photo_file_path: '',
});

watch(
    () => props.promo,
    (promo) => {
        if (!promo) return;

        form.id = promo.id ?? '';
        form.title = promo.title ?? '';
        form.description = promo.description ?? '';
        form.valid_start_date = promo.valid_start_date ?? '';
        form.valid_end_date = promo.valid_end_date ?? '';
        form.photo_file_path = promo.photo_file_path ?? '';
        form.photo_file = null;
    },
    { immediate: true },
);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            form.clearErrors();
        }
    },
);

const submit = () => {
    form.post(update({ promo: form.id }).url, {
        method: 'put',
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.update_promo"
        icon="lucide:megaphone"
        variant="default"
        confirmLabel="action.update"
        @submit="submit"
        @close="$emit('close')"
    >
        <form @submit.prevent="submit" class="flex flex-col gap-y-4">
            <div class="flex flex-col items-center justify-center gap-y-2">
                <InputSingleImageFile
                    v-model="form.photo_file"
                    :photo_file_path="form.photo_file_path"
                    :error="form.errors.photo_file"
                    @update:error="form.errors.photo_file = $event ?? undefined"
                />
            </div>
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.title"
                    type="text"
                    placeholder="form.placeholder.title"
                    v-model:modelValue="form.title"
                    :error="form.errors.title"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputTextAreaWithLabel
                    label="form.label.description"
                    placeholder="form.placeholder.description"
                    v-model:modelValue="form.description"
                    :error="form.errors.description"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputDateAndDate
                    label="form.label.validity_period"
                    v-model:date_first="form.valid_start_date"
                    v-model:date_second="form.valid_end_date"
                    :error="form.errors.valid_start_date || form.errors.valid_end_date"
                    :allow-past-date="true"
                />
            </div>
        </form>
    </ModalForm>
</template>
