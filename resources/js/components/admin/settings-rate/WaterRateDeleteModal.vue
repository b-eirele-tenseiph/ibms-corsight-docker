<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/admin/management/settings/rates/water';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    show: boolean;
    rate?: any;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const deleteSwitchbot = reactive({
    onSubmit: () => {
        router.delete(destroy({ rate: props.rate?.id }).url, {
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;
</script>
<template>
    <ModalForm
        :open="props.show"
        title="modal.action.delete_rate"
        confirmLabel="action.delete"
        icon="material-symbols:account-box-outline"
        variant="destructive"
        @close="emit('close')"
        @submit="deleteSwitchbot.onSubmit"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="normal overflow-hidden font-semibold">{{ $t('modal.confirm.delete_water_rate') }}</p>
            <p class="text-destructive font-semibold">{{ $t('modal.confirm.warning') }}</p>
            <div class="flex flex-col gap-2 capitalize">
                <label>
                    <span class="font-semibold">{{ $t('form.label.consumer_type') }}:</span> {{ props.rate?.consumer_type }}
                </label>
                <label>
                    <span class="font-semibold">{{ $t('form.label.meter_size') }}:</span> {{ props.rate?.meter_size }}"
                </label>
            </div>
        </div>
    </ModalForm>
</template>
