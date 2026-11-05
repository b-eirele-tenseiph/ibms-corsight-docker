<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { deleteMethod } from '@/routes/crew/foods';
import type { Food } from '@/types/t-food';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    food: Food | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit'): void;
}>();

const form = useForm({});
const isProcessing = computed(() => form.processing);

function onClose() {
    form.reset();
    emit('close');
}

function onSubmit() {
    if (!props.food?.id) return;
    form.delete(deleteMethod(props.food.id).url, {
        onSuccess: () => {
            form.reset();
            emit('submit');
            emit('close');
        },
    });
}
</script>

<template>
    <ModalForm
        :open="props.isOpen"
        title="modal.action.delete_menu"
        :isProcessing="isProcessing"
        :confirmLabel="'action.delete'"
        @close="onClose"
        @submit="onSubmit"
        icon="heroicons-outline:exclamation"
        variant="destructive"
    >
        <p class="text-primary-text text-sm font-medium">{{ $t('modal.confirm.delete_menu') }}</p>
        <p class="text-destructive mt-2 text-sm font-semibold">{{ $t('modal.confirm.delete_device_body') }}</p>

        <!-- Replaced details -->
        <div class="mt-4 text-sm">
            <p>
                <span class="text-primary-text font-semibold">{{ $t('form.label.food_name') }}:</span>
                <span class="ml-1">{{ props.food?.name || '—' }}</span>
            </p>
            <p class="mt-1">
                <span class="text-primary-text font-semibold">{{ $t('form.label.category') }}:</span>
                <span class="ml-1">{{ props.food?.category?.name || '—' }}</span>
            </p>
        </div>
    </ModalForm>
</template>
