<script setup lang="ts">
import { ScenePanel } from '@/components/shared/scenes';
import { Icon } from '@iconify/vue';
import { inject } from 'vue';

defineProps<{
    scene: any;
    editMode: boolean;
}>();

const theme = inject<{
    palettes: Record<string, { bg: string; text: string; border: string }>;
}>('theme');
</script>

<template>
    <div class="flex max-w-16 flex-col items-center justify-center gap-1">
        <div
            :class="[
                'relative flex size-10 cursor-pointer items-center justify-center rounded-full border transition duration-200 ease-in-out hover:scale-105 hover:shadow-sm hover:brightness-95',
                theme?.palettes[scene.context?.palette]?.bg,
                theme?.palettes[scene.context?.palette]?.text,
                theme?.palettes[scene.context?.palette]?.border,
                editMode && 'animate-shaking',
            ]"
        >
            <Icon :icon="scene.context.icon" class="size-5" />

            <!-- Edit scene button overlay -->
            <ScenePanel>
                <template #trigger>
                    <span
                        v-if="editMode"
                        :class="[
                            theme?.palettes[scene.context?.palette]?.bg,
                            'absolute inset-0 z-10 flex cursor-pointer items-center justify-center rounded-full',
                        ]"
                    >
                        <Icon icon="line-md:edit" class="size-4" />
                    </span>
                </template>

                <!-- <template #content>
                    <SceneEditPanel />
                </template> -->
            </ScenePanel>
        </div>

        <span class="text-secondary-text w-full truncate text-center text-xs font-semibold">
            {{ scene.name }}
        </span>
    </div>
</template>
