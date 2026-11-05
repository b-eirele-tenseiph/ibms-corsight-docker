<script setup lang="ts">
import { InputDateAndDate, InputSingleImageFile, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { store } from '@/routes/staff/promo_management';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const emit = defineEmits(['close', 'submit']);
const props = defineProps<{
    open: boolean;
}>();

const form = useForm({
    title: '',
    description: '',
    valid_start_date: '',
    valid_end_date: '',
    photo_file: null as File | null,
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            form.clearErrors();
        }
    },
);

const submit = () => {
    form.post(store.url(), {
        forceFormData: true,
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
        title="modal.action.add_promo"
        icon="lucide:megaphone"
        variant="default"
        confirmLabel="action.create"
        @submit="submit"
        @close="$emit('close')"
    >
        <form @submit.prevent="submit" class="flex flex-col gap-y-4">
            <div class="flex justify-center">
                <InputSingleImageFile v-model="form.photo_file" :error="form.errors.photo_file" />
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
