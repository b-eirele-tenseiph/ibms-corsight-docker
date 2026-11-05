<script setup lang="ts">
import SheetOverlay from '@/components/ui/sheet/SheetOverlay.vue';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { reactiveOmit } from '@vueuse/core';
import { DialogClose, DialogContent, type DialogContentEmits, type DialogContentProps, DialogPortal, useForwardPropsEmits } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
interface SheetContentProps extends DialogContentProps {
    class?: HTMLAttributes['class'];
    side?: 'top' | 'right' | 'bottom' | 'left';
}
defineOptions({
    inheritAttrs: false,
});
const props = withDefaults(defineProps<SheetContentProps>(), {
    side: 'right',
});
const emits = defineEmits<DialogContentEmits>();
const delegatedProps = reactiveOmit(props, 'class', 'side');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DialogPortal>
        <SheetOverlay />
        <DialogContent
            data-slot="sheet-content"
            :class="
                cn(
                    'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out fixed z-50 flex flex-col gap-4 overflow-auto p-4 shadow-lg transition ease-in-out data-[state=closed]:duration-300 data-[state=open]:duration-500 sm:w-3/4 sm:rounded-l-2xl lg:w-2/5 xl:w-1/3',
                    side === 'right' &&
                        'data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right inset-y-0 right-0 h-full w-full border-l',
                    side === 'left' && 'data-[state=closed]:slide-out-to-left data-[state=open]:slide-in-from-left inset-y-0 left-0 h-full border-r',
                    side === 'top' && 'data-[state=closed]:slide-out-to-top data-[state=open]:slide-in-from-top inset-x-0 top-0 h-auto border-b',
                    side === 'bottom' &&
                        'data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom inset-x-0 bottom-0 h-auto border-t',
                    props.class,
                )
            "
            v-bind="{ ...forwarded, ...$attrs }"
        >
            <slot />

            <DialogClose
                class="ring-offset-background data-[state=open]:bg-secondary absolute top-5 left-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none"
            >
                <Icon icon="gravity-ui:arrow-right-to-line" class="text-secondary-text size-6 cursor-pointer" />
                <span class="sr-only">Close</span>
            </DialogClose>
        </DialogContent>
    </DialogPortal>
</template>
