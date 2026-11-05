<script setup lang="ts">
import { SceneAddPanel, ScenePanel } from '@/components/shared/scenes';
import { Button } from '@/components/ui/button';
import { Room } from '@/types/t-room';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import { ScenesInputSearch, ScenesRoomFilter } from '.';

defineProps<{
    rooms: Room[];
    filters: Record<string, any>;
}>();
const closeModal = ref(false);
</script>

<template>
    <div class="flex flex-row justify-between gap-4">
        <div class="xs:flex-row flex w-full flex-col gap-2">
            <!-- Search Input -->
            <ScenesInputSearch :filters />

            <!-- Filter Dropdown -->
            <ScenesRoomFilter :rooms :filters />
        </div>

        <!--Add scene button-->
        <ScenePanel v-model:open="closeModal" class="w-full gap-0 sm:w-3/4 sm:max-w-lg sm:rounded-l-lg">
            <template #trigger>
                <Button @click="closeModal = false">
                    <Icon icon="mdi:plus" class="size-4 md:mr-2" />
                    <span class="fluid-text-xs-sm hidden md:block">{{ $t('modal.action.add_scene') }}</span>
                </Button>
            </template>
            <template #content>
                <SceneAddPanel :rooms :admin="true" @close="closeModal = false" />
            </template>
        </ScenePanel>
    </div>
</template>
