<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { destroy } from '@/routes/admin/management/corsight/accounts';
import { CorsightAccountData } from '@/types';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    show: boolean;
    account?: Pick<CorsightAccountData, 'username' | 'id'>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const deleteCorsight = reactive({
    onSubmit: () => {
        router.delete(destroy({ account: props.account!.id }).url, {
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
        @close="emit('close')"
        @submit="deleteCorsight.onSubmit"
        title="modal.action.delete_corsight_account"
        confirmLabel="action.delete"
        icon="lucide:scan-face"
        variant="destructive"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="normal overflow-hidden font-semibold">{{ $t('modal.confirm.delete_corsight_account') }}</p>
            <p class="text-destructive font-semibold">{{ $t('modal.confirm.warning') }}</p>
            <label>
                <span class="font-semibold">{{ $t('form.label.username') }}:</span> {{ account?.username }}
            </label>
        </div>
    </ModalForm>
</template>
