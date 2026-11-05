```
<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { ModalVariants, modalVariants } from '.';

defineProps<{
    open: boolean;
    title: string | null;
    icon?: string;
    variant?: ModalVariants['variant'];
}>();

defineEmits(['close', 'submit']);
</script>

<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent :class="cn(modalVariants({ variant }), 'flex w-full flex-col border-0 px-5 ring-0 outline-none')">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-x-4">
                    <div v-if="icon" class="bg-accent rounded-sm px-2 py-1.5 shadow-sm">
                        <Icon :icon="icon" class="size-5 rounded" />
                    </div>
                    <p class="text-primary-text fluid-text-xs-base font-bold">{{ title }}</p>
                </DialogTitle>
            </DialogHeader>

            <div class="fluid-text-xs-sm flex-1 overflow-y-auto px-2">
                <slot />
                <DialogDescription class="sr-only"> </DialogDescription>
            </div>
        </DialogContent>
    </Dialog>
</template>
