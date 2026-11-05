<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { Button } from '@/components/ui/button';
import { triggerEmergency } from '@/routes/user/emergency';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

withDefaults(
    defineProps<{
        open: boolean;
        loading?: boolean;
    }>(),
    { loading: false },
);

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();

const processing = ref(false);
const sendAlert = () => {
    processing.value = true;

    // Send the command to the server
    useForm({
        command: 'doorbellMode',
    }).post(triggerEmergency.url(), {
        onFinish: () => {
            processing.value = false;
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.emergency_alert"
        icon="lucide:siren"
        confirmLabel="modal.action.send_alert"
        variant="destructive"
        :closeOnOverlayClick="false"
        :isBlocked="processing"
    >
        <template #header>
            <div class="flex flex-col items-center gap-3 p-5">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-500">
                    <Icon icon="pepicons-pop:exclamation" class="text-page-background h-8 w-8" />
                </div>
                <h3 class="text-primary-text text-center text-lg font-bold">
                    {{ $t('modal.action.emergency_alert') }}
                </h3>
            </div>
        </template>

        <!-- Body -->
        <div class="px-6 text-center text-sm sm:text-base">
            {{ $t('modal.confirm.emergency_body') }}
        </div>

        <template #footer>
            <div class="sticky z-10 p-2 pt-2 inset-shadow-2xs">
                <div class="flex justify-center">
                    <div class="flex items-center gap-2">
                        <!-- Red primary button first -->
                        <Button
                            type="button"
                            :disabled="processing"
                            @click="sendAlert"
                            tabindex="-1"
                            class="w-auto flex-none bg-red-500 transition-none hover:bg-red-500! hover:opacity-100!"
                        >
                            {{ $t('action.send_alert') }}
                        </Button>

                        <!-- Outline cancel second -->
                        <Button
                            type="button"
                            :disabled="processing"
                            @click="emit('close')"
                            tabindex="-1"
                            class="text-secondary-text border-border bg-backgroy=und inline-flex items-center rounded-md border px-4 py-2 font-medium hover:bg-gray-50 disabled:opacity-50"
                        >
                            {{ $t('action.cancel') }}
                        </Button>
                    </div>
                </div>
            </div>
        </template>
    </ModalForm>
</template>
