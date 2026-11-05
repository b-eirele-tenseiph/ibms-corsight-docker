<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/admin/management/rooms';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Props passed to the modal
const props = defineProps<{
    open: boolean;
    room?: { id: number; name?: string };
}>();

// Emit event for closing the modal
const emit = defineEmits<{
    (e: 'close'): void;
}>();

// Holds any server-side error messages preventing deletion
const errors = ref<string[] | null>(null);

// Closes the modal and resets errors
function close() {
    emit('close');
    errors.value = null;
}

// Sends delete request via Inertia router
function confirmDelete() {
    if (!props.room) return;

    router.delete(destroy(String(props.room.id)).url, {
        onSuccess: () => {
            close();
        },
    });
}
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.delete_room"
        icon="lucide:triangle-alert"
        confirmLabel="action.delete"
        :closeOnOverlayClick="false"
        variant="destructive"
        @close="close"
        @submit="confirmDelete"
    >
        <div class="flex flex-col space-y-2">
            <label class="text-foreground text-sm font-bold">
                {{ $t('modal.confirm.delete_room') }}
            </label>
            <label class="text-destructive py-2 text-sm font-semibold">
                {{ $t('modal.confirm.warning') }}
            </label>
            <label class="text-sm">
                <span class="text-muted-foreground font-semibold">{{ $t('form.label.room_name') }}:</span>
                <span class="text-foreground ml-1 font-medium">{{ props.room?.name || 'Untitled' }}</span>
            </label>
        </div>

        <!-- Deletion Blockers -->
        <div v-if="errors?.length" class="mt-4 rounded-md border border-red-300 bg-red-50 p-3">
            <p class="text-destructive mb-2 font-semibold">{{ $t('modal.alert.room_error') }}</p>
            <ul class="text-destructive list-disc pl-5 text-sm">
                <li v-for="(reason, index) in errors" :key="index">
                    {{ reason }}
                </li>
            </ul>
        </div>
    </ModalForm>
</template>
