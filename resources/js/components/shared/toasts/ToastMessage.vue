<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { type FlashMessage } from '@/types';
import { inject, watch } from 'vue';
import 'vue-sonner/style.css';

const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

const props = defineProps<{
    flash?: Record<string, FlashMessage>;
}>();

function showFlashToast(message: FlashMessage) {
    if (!message) return;

    switch (message.type) {
        case 'success':
            toast.success(message.body, {
                classes: {
                    toast: 'border-l-3 border-l-success',
                    icon: 'text-success [&>svg]:size-4 xs:[&>svg]:size-5',
                },
            });
            break;
        case 'error':
            toast.error(message.body, {
                classes: {
                    toast: 'border-l-3 border-l-error',
                    icon: 'text-error [&>svg]:size-4 xs:[&>svg]:size-5',
                },
            });
            break;
        case 'info':
            toast.info(message.body, {
                classes: {
                    toast: 'border-l-3 border-l-info',
                    icon: 'text-info [&>svg]:size-4 xs:[&>svg]:size-5',
                },
            });
            break;
        case 'warning':
            toast.warning(message.body, {
                classes: {
                    toast: 'border-l-3 border-l-warning',
                    icon: 'text-warning [&>svg]:size-4 xs:[&>svg]:size-5',
                },
            });
            break;
        default:
            toast.message(message.body);
    }
}

watch(
    () => props.flash,
    (newFlash) => {
        if (newFlash) {
            Object.values(newFlash).forEach((msg) => showFlashToast(msg));
        }
    },
);
</script>

<template>
    <Toaster
        closeButton
        position="bottom-right"
        mobileOffset="18px"
        :toastOptions="{
            unstyled: true,
            classes: {
                toast: 'flex items-center gap-2 bg-popover px-4 py-3 rounded-lg shadow-md border border-border min-[600px]:min-w-md w-full',
                content: 'flex-1 truncate',
                title: 'font-medium text-sm text-popover-foreground fluid-text-xs-sm',
                closeButton: 'order-last cursor-pointer hover:bg-accent p-1 rounded',
            },
        }"
    />
</template>
