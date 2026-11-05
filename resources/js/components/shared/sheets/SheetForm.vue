<script setup lang="ts">
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetClose, SheetContent, SheetHeader } from '@/components/ui/sheet';
import { Icon } from '@iconify/vue';

withDefaults(
    defineProps<{
        open: boolean;
        closeOnOverlayClick?: boolean;
        customClosable?: boolean;
    }>(),
    {
        closeOnOverlayClick: true,
    },
);

defineEmits(['close']);
</script>

<template>
    <Sheet
        :open="open"
        @update:open="
            (val) => {
                if (!val && closeOnOverlayClick) $emit('close');
            }
        "
    >
        <SheetContent
            :customClosable="!customClosable"
            class="3xl:min-w-[700px] bg-card w-full gap-y-0 rounded-tl-[8px] rounded-bl-[8px] sm:min-w-[500px] xl:min-w-[600px]"
        >
            <SheetHeader class="flex flex-row justify-between">
                <SheetClose v-if="customClosable" as-child class="cursor-pointer">
                    <Icon icon="lucide:arrow-right-to-line" class="text-secondary-text h-5.5 w-5.5" />
                </SheetClose>
            </SheetHeader>

            <hr class="border-secondary-light-gray" />

            <ScrollArea class="h-screen px-6">
                <slot />
            </ScrollArea>
        </SheetContent>
    </Sheet>
</template>
