<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/staff/promo_management';
import { Promo } from '@/types';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close']);

const props = defineProps<{
    open: boolean;
    promo: Promo | null;
}>();

const submit = () => {
    if (!props.promo) return;

    useForm({}).delete(destroy({ promo: props.promo.id }).url, {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.delete_promo"
        icon="lucide:triangle-alert"
        variant="destructive"
        confirmLabel="action.delete"
        @submit="submit"
        @close="$emit('close')"
        iconColor="text-error"
    >
        <form @submit.prevent="submit" class="flex flex-col gap-y-4">
            <div class="text-sm font-bold">
                <p>
                    {{ $t('modal.confirm.delete_promo') }}
                </p>
            </div>

            <div class="flex flex-col gap-y-1.5 text-sm">
                <p class="text-error text-sm font-semibold">
                    {{ $t('modal.confirm.warning') }}
                </p>
                <p class="font-regular">
                    <span class="font-semibold">{{ $t('form.label.promo_title') }}:</span>
                    {{ props.promo?.title }}
                </p>
            </div>
        </form>
    </ModalForm>
</template>
