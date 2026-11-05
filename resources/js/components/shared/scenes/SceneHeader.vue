<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { SheetClose } from '@/components/ui/sheet';
import { useModal } from '@/composables/useModal';
import { Icon } from '@iconify/vue';
import { SceneDeletePanel } from '.';

defineProps<{
    label: string;
    scene: any;
    admin?: boolean;
}>();

const emit = defineEmits<{
    (e: 'primaryAction'): void;
}>();

const DELETE_MODAL = useModal();
</script>

<template>
    <div class="flex items-center justify-between border-b px-6 py-4">
        <SheetClose>
            <Icon icon="lucide:arrow-right-to-line" class="text-secondary-text size-5 cursor-pointer" />
        </SheetClose>

        <div class="flex flex-row items-center gap-2">
            <Icon
                v-if="label.toLowerCase().includes('update') && !admin"
                icon="lucide:trash-2"
                class="size-5 cursor-pointer"
                @click="DELETE_MODAL.open"
            />
            <Button size="sm" @click="emit('primaryAction')">
                <span class="fluid-text-xs-sm px-2">{{ $t(`action.${label}`) }}</span>
            </Button>
        </div>
    </div>

    <SceneDeletePanel :scene :open="DELETE_MODAL.visible.value" @close="DELETE_MODAL.close" />
</template>
