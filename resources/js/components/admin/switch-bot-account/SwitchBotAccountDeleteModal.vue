<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { destroy } from '@/routes/admin/management/switchbot/accounts';
import { SwitchBotAccountData } from '@/types';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    show: boolean;
    account?: Pick<SwitchBotAccountData, 'email' | 'id' | 'room'>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const deleteSwitchbot = reactive({
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
        @submit="deleteSwitchbot.onSubmit"
        title="modal.action.delete_switch_bot_account"
        confirmLabel="action.delete"
        icon="material-symbols:account-box-outline"
        variant="destructive"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="normal overflow-hidden font-semibold">{{ $t('modal.confirm.delete_switch_bot_account') }}</p>
            <p class="text-destructive font-semibold">{{ $t('modal.confirm.warning') }}</p>
            <div class="flex flex-col gap-2">
                <label
                    ><span class="font-semibold">{{ $t('form.label.room_name') }}:</span> {{ account?.room?.name }}</label
                >
                <label
                    ><span class="font-semibold">{{ $t('form.label.email_address') }}:</span> {{ account?.email }}</label
                >
            </div>
        </div>
    </ModalForm>
</template>
