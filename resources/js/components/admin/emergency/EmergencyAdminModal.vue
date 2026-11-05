<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import Button from '@/components/ui/button/Button.vue';
import { readOne } from '@/routes/notifications';
import { EmergencyBroadcastNotification, SharedData } from '@/types';
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { useEchoNotification } from '@laravel/echo-vue';
import { onMounted, onUnmounted, ref } from 'vue';

const page = usePage<SharedData>();
const authUser = page.props.auth.user;

const isOpen = ref(false);

const notification = ref({
    message: '',
    url: '',
    id: '',
});

const processing = ref(false);

function view() {
    const { id, url } = notification.value;
    if (!id || !url) return (isOpen.value = false);

    processing.value = true;

    router.put(
        readOne(id).url,
        { url },
        {
            preserveState: false,
            onFinish: () => {
                processing.value = false;
                isOpen.value = false;
            },
        },
    );
}

const { stopListening: stopNotification, listen: listenNotification } = useEchoNotification<EmergencyBroadcastNotification>(
    `admin.notification.${authUser.id}`,
    (n) => {
        if (n.type === 'App\\Notifications\\AdminNotification' && n.data.title?.toLowerCase() === 'emergency') {
            notification.value = { message: n.data.message, url: n.data.url, id: n.id };
            isOpen.value = true;
        }
    },
);

onMounted(() => {
    // subscribe to notifications
    listenNotification();
});

onUnmounted(() => {
    stopNotification();
});
</script>

<template>
    <ModalForm
        :open="isOpen"
        title="modal.action.emergency_alert"
        icon="lucide:siren"
        confirmLabel="modal.action.view"
        variant="destructive"
        :isBlocked="processing"
        :closeOnOverlayClick="false"
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

        <!-- Body: show the message -->
        <div class="px-6 text-center text-sm whitespace-pre-line sm:text-base">
            {{ notification.message }}
        </div>

        <template #footer>
            <div class="flex justify-center gap-2 p-3">
                <Button type="submit" :disabled="processing" class="w-auto flex-none bg-red-500 hover:!bg-red-500 hover:!opacity-100" @click="view">
                    {{ $t('modal.action.view') }}
                </Button>
                <Button variant="outline" type="button" :disabled="processing" @click="isOpen = false" class="w-auto flex-none hover:opacity-90">
                    {{ $t('modal.action.dismiss') }}
                </Button>
            </div>
        </template>
    </ModalForm>
</template>
