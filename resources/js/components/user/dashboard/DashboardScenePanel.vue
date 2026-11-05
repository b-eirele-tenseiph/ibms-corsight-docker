<script setup lang="ts">
import { SceneAddPanel } from '@/components/shared/scenes';
import ScenePanel from '@/components/shared/scenes/ScenePanel.vue';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import { SceneItem } from '.';

defineProps<{ room: any }>();
const isEditMode = ref(false);
const closeModal = ref(false);
</script>
<template>
    <div class="flex w-full flex-col px-2">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold sm:text-base">{{ $t('page.scene_management.label.scene') }}</span>
            <Icon
                @click="isEditMode = !isEditMode"
                :icon="isEditMode ? 'tabler:settings-off' : 'tabler:settings-cog'"
                class="text-secondary-text size-4 cursor-pointer"
            />
        </div>

        <div class="flex flex-row gap-4 pt-2">
            <!--List of available scenes within room-->
            <ScrollArea class="flex overflow-x-auto">
                <div class="flex gap-4 p-1">
                    <SceneItem v-for="scene in room.scenes" :key="scene.id" :scene="scene" :editMode="isEditMode" :rooms="[room]" />
                </div>
                <ScrollBar orientation="horizontal" />
            </ScrollArea>

            <!--Add scene button-->
            <ScenePanel v-model:open="closeModal" class="w-full gap-0 pt-2 sm:w-3/4 sm:max-w-lg sm:rounded-l-lg">
                <template #trigger>
                    <SceneItem
                        :scene="{ name: $t('action.add'), context: { icon: 'lucide:plus' } }"
                        :rooms="[room]"
                        :editMode="false"
                        @click="
                            isEditMode = false;
                            closeModal = false;
                        "
                    />
                </template>
                <template #content>
                    <SceneAddPanel :rooms="room" @close="closeModal = false" />
                </template>
            </ScenePanel>
        </div>
    </div>
</template>
