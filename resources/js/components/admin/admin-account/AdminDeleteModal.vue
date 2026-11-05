<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/admin/management/accounts/admins';
import { Admin } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Props passed to the modal
const props = defineProps<{
    open: boolean;
    admin: Admin;
}>();

// Emit event for closing the modal
const emit = defineEmits<{
    (e: 'close'): void;
}>();

// Holds any server-side error messages preventing deletion
const errors = ref<string[] | null>(null);

const loading = ref(false);

const isBlocked = computed(() => loading.value);

// Closes the modal and resets errors
function close() {
    emit('close');
    errors.value = null;
}

// Sends delete request via Inertia router
function confirmDelete() {
    loading.value = true;
    if (!props.admin) return;

    router.delete(destroy(String(props.admin.id)).url, {
        onSuccess: () => {
            loading.value = false;
            close();
        },
    });
}
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.delete_admin"
        icon="lucide:triangle-alert"
        :confirmLabel="'action.delete'"
        :closeOnOverlayClick="false"
        :isBlocked="isBlocked"
        variant="destructive"
        @close="close"
        @submit="confirmDelete"
    >
        <div class="flex flex-col space-y-2">
            <label class="text-foreground fluid-text-xs-sm font-bold">
                {{ $t('modal.confirm.delete_admin') }}
            </label>
            <label class="text-destructive fluid-text-xs-sm py-2 font-semibold">
                {{ $t('modal.confirm.warning') }}
            </label>
            <label class="fluid-text-xs-sm">
                <span class="text-muted-foreground font-semibold">{{ $t('form.label.username') }}:</span>
                <span class="text-foreground ml-1 font-medium">{{ admin.username }}</span>
            </label>
            <label class="fluid-text-xs-sm">
                <span class="text-muted-foreground font-semibold">{{ $t('form.label.name') }}:</span>
                <span class="text-foreground ml-1 font-medium">{{ admin.name }}</span>
            </label>
        </div>
    </ModalForm>
</template>
