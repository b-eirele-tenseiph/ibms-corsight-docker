<script setup lang="ts">
import { Button, ButtonVariants } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Icon } from '@iconify/vue';

withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        icon?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        variant?: ButtonVariants['variant'];
        closeOnOverlayClick?: boolean;
        isBlocked?: boolean;
        isCancelBlocked?: boolean;
    }>(),
    {
        variant: 'default',
        closeOnOverlayClick: true,
        isBlocked: false,
        isCancelBlocked: false,
    },
);

defineEmits(['close', 'submit']);
</script>

<template>
    <Dialog
        :open="open"
        @update:open="
            (val) => {
                if (!val && closeOnOverlayClick) $emit('close');
            }
        "
    >
        <DialogContent
            class="bg-background tiny:max-h-[400px] xxs:max-h-[600px] fixed top-1/2 left-1/2 z-50 flex w-full -translate-x-1/2 -translate-y-1/2 scale-90 transform flex-col gap-2 rounded-lg p-0 shadow-lg focus-visible:outline-none sm:max-w-[600px] sm:scale-100"
        >
            <!-- Header -->
            <slot name="header">
                <DialogDescription class="sr-only"></DialogDescription>
                <div class="sticky top-0 z-10 px-5 py-4">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-x-4">
                            <div class="bg-accent rounded-[8px] p-2 shadow-sm">
                                <Icon
                                    v-if="icon"
                                    class="size-5 rounded"
                                    :icon="variant === 'destructive' ? 'lucide:triangle-alert' : icon"
                                    :color="variant === 'destructive' ? 'var(--color-destructive)' : 'var(--primary-text)'"
                                />
                            </div>
                            <p class="text-primary-text fluid-text-xs-base text-start font-bold">{{ $t(title) }}</p>
                        </DialogTitle>
                    </DialogHeader>
                </div>
            </slot>

            <!-- Scrollable Body -->
            <div class="max-h-[600px] flex-1 overflow-y-auto px-6 pb-2 text-sm break-words sm:text-base">
                <slot />
            </div>

            <!-- Footer -->
            <slot name="footer">
                <div class="sticky z-10 p-2 pt-2 inset-shadow-2xs">
                    <DialogFooter class="flex justify-end">
                        <div class="flex flex-row items-center justify-end gap-2 pr-2.5">
                            <Button
                                class="w-auto flex-none hover:cursor-pointer"
                                variant="outline"
                                type="button"
                                @click="$emit('close')"
                                :disabled="isCancelBlocked"
                            >
                                {{ cancelLabel ? $t(cancelLabel) : $t('action.cancel') }}
                            </Button>
                            <Button
                                v-if="confirmLabel"
                                :variant="variant"
                                type="submit"
                                :disabled="isBlocked"
                                @click="$emit('submit')"
                                class="hover:cursor-pointer hover:opacity-90"
                            >
                                {{ $t(confirmLabel) }}
                            </Button>
                        </div>
                    </DialogFooter>
                </div>
            </slot>
        </DialogContent>
    </Dialog>
</template>
