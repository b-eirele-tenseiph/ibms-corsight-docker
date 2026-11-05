<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { Label } from '@/components/ui/label';
import { destroy } from '@/routes/admin/management/lorawan/gateways';
import { LorawanGateway } from '@/types/t-lorawan-gateway';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Props passed to the modal
const props = defineProps<{
    open: boolean;
    gateway: LorawanGateway;
}>();

// Emit event for closing the modal
const emit = defineEmits<{
    (e: 'close'): void;
}>();

const loading = ref(false);

const isBlocked = computed(() => loading.value || props.gateway.devices_count > 0);

// Closes the modal and resets errors
function close() {
    emit('close');
}

// Sends delete request via Inertia router
function confirmDelete() {
    loading.value = true;
    if (!props.gateway) return;

    router.delete(destroy(props.gateway.id).url, {
        onSuccess: () => {
            close();
        },
    });
}
</script>

<template>
    <ModalForm
        :open="open"
        :title="'modal.action.delete_lorawan_gateway'"
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
                {{ $t('modal.confirm.delete_lorawan_gateway') }}
            </label>
            <label class="text-destructive fluid-text-xs-sm py-2 font-semibold">
                {{ $t('modal.confirm.warning') }}
            </label>
            <label class="fluid-text-xs-sm">
                <span class="text-muted-foreground font-semibold">{{ $t('form.label.ip_address') }}:</span>
                <span class="text-foreground ml-1 font-medium">{{ gateway.ip_address }}</span>
            </label>

            <div v-if="gateway.devices_count > 0" class="bg-secondary fluid-text-xs-sm rounded-md p-2">
                <Label class="text-destructive py-2 font-semibold">{{ $t('modal.alert.lorawan_gateway_error') }}</Label>
                <ul class="list-inside list-disc">
                    <li>{{ $t('modal.alert.device_registered') }}</li>
                </ul>
            </div>
        </div>
    </ModalForm>
</template>
