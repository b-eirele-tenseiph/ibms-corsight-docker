<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { destroy } from '@/routes/scenes';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    open: boolean;
    scene?: any;
}>();

const emit = defineEmits(['close']);

const deleteSwitchbot = reactive({
    onSubmit: () => {
        router.delete(destroy({ scene: props.scene?.id }).url, {
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;
</script>
<template>
    <ModalForm
        :open="props.open"
        @close="emit('close')"
        @submit="deleteSwitchbot.onSubmit"
        title="modal.action.delete_scene"
        confirmLabel="action.delete"
        icon="material-symbols:account-box-outline"
        variant="destructive"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="normal overflow-hidden font-semibold">{{ $t('modal.confirm.delete_scene') }}</p>
            <p class="text-destructive font-semibold">{{ $t('modal.confirm.warning') }}</p>
            <div class="flex flex-col gap-2">
                <label>
                    <span class="font-semibold">{{ $t('form.label.scene_name') }}: </span>{{ scene?.name }}</label
                >
                <label v-if="scene?.room">
                    {{ $t('form.label.room_name') }}: <span class="font-semibold"> {{ scene?.room?.name }}</span>
                </label>
            </div>
        </div>
    </ModalForm>
</template>
