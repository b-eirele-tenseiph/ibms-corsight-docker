<script setup lang="ts">
import { SceneEditPanel, ScenePanel } from '@/components/shared/scenes';
import { commandExecute } from '@/routes/user/scenes';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { inject, ref } from 'vue';

defineProps<{
    scene: any;
    editMode: boolean;
    rooms: any;
}>();

const closeModal = ref(false);
const isLoading = ref(false);

const theme = inject<{
    palettes: Record<string, { bg: string; text: string; border: string }>;
}>('theme');

// Scene Execution
const executeCommand = async (sceneId: any) => {
    isLoading.value = true;
    router.post(
        commandExecute({ scene: sceneId }).url,
        {},
        {
            onFinish: () => {
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        },
    );
};
//#endregion Scene Panel
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
                isLoading && 'opacity-30',
            ]"
        >
            <Icon :icon="scene.context.icon" class="size-5" @click="executeCommand(scene.id)" />

            <!-- Edit scene button overlay -->
            <ScenePanel v-model:open="closeModal">
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

                <template #content>
                    <SceneEditPanel :rooms :scene @close="closeModal = false" />
                </template>
            </ScenePanel>
        </div>

        <span class="text-secondary-text w-full truncate text-center text-xs font-semibold">
            {{ scene.name }}
        </span>
    </div>
</template>
