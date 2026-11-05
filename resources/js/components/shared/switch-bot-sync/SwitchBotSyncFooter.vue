<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DialogClose } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import type { HTMLAttributes } from 'vue';
const props = defineProps<{
    class?: HTMLAttributes['class'];
    loading: boolean;
    isError?: boolean;
    applying?: boolean;
}>();
defineEmits<{ applyChanges: [] }>();
</script>

<template>
    <div data-slot="dialog-footer" :class="cn('flex shrink-0 flex-col gap-2 px-0 sm:flex-row sm:justify-end lg:items-start', props.class)">
        <slot />
        <div>
            <div class="mb-4 flex flex-row justify-end gap-4">
                <Label class="flex items-center gap-1 text-xs font-normal">
                    <Icon icon="mdi:circle" class="text-sync-modal-label-green size-5" />{{ $t('label.new') }}
                </Label>
                <Label class="flex items-center gap-1 text-xs font-normal">
                    <Icon icon="mdi:circle" class="text-sync-modal-label-red size-5" />
                    {{ $t('label.outdated') }}
                </Label>
                <Label class="flex items-center gap-1 text-xs font-normal">
                    <Icon icon="mdi:circle" class="text-sync-modal-label-blue size-5" />{{ $t('label.updated') }}
                </Label>
            </div>
            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <DialogClose>
                    <Button variant="secondary" class="fluid-text-xs-sm cursor-pointer">{{ $t('action.cancel') }}</Button>
                </DialogClose>
                <Button
                    @click="$emit('applyChanges')"
                    :disabled="props.loading || !!props.applying || !!isError"
                    class="fluid-text-xs-sm cursor-pointer"
                    >{{ $t('action.apply_changes') }}</Button
                >
            </div>
        </div>
    </div>
</template>
