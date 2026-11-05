<script setup lang="ts">
import { DialogOverlay } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { DialogContent, type DialogContentEmits, type DialogContentProps, DialogPortal, useForwardPropsEmits } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';
import { SwitchBotSyncFooter, SwitchBotSyncHeader } from '.';

const props = defineProps<
    DialogContentProps & {
        class?: HTMLAttributes['class'];
        loading: boolean;
        applying?: boolean;
        header: string;
        title: string;
        description: string;
        isError?: boolean;
        failedAccounts: any[];
    }
>();
const emits = defineEmits<DialogContentEmits & { applyChanges: [] }>();
const delegatedProps = computed(() => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { class: _, ...delegated } = props;
    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DialogPortal>
        <DialogOverlay />
        <DialogContent
            data-slot="dialog-content"
            v-bind="forwarded"
            :class="
                cn(
                    'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed top-[50%] left-[50%] z-50 grid h-100 w-9/10 translate-x-[-50%] translate-y-[-50%] gap-4 overflow-auto rounded-lg border p-6 shadow-lg duration-200 sm:w-5/6 lg:h-auto lg:w-1/2',
                    props.class,
                )
            "
        >
            <SwitchBotSyncHeader :header :title :description :failedAccounts />

            <slot />

            <SwitchBotSyncFooter @applyChanges="$emit('applyChanges')" :loading :applying :isError />
        </DialogContent>
    </DialogPortal>
</template>
