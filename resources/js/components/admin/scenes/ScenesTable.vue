<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SceneDeletePanel, SceneEditPanel, ScenePanel } from '@/components/shared/scenes';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { useModal } from '@/composables/useModal';
import { useResizeObserverMap } from '@/composables/useResizeObserverMap';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import { formatScheduleDisplay } from '@/lib/utils';
import { index } from '@/routes/admin/management/settings/scenes';
import { PaginatedResponse, Scene } from '@/types';
import { Room } from '@/types/t-room';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    scenes: PaginatedResponse<Scene>;
    filters: Record<string, any>;
    rooms: Room[];
}>();

const { t } = useI18n();

const filters = ref(props.filters);
const { isExtraSmallMobile, isTablet, isSmallMobile, isMobile } = useBreakpoints();
const expandedSceneDevices = ref<Record<number, boolean>>({});

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.scenes.data.length > 0 || isSmallScreen.value ? props.scenes.data : [{}];
});

const processedScenes = computed(() => {
    if (!props.scenes?.data) return [];
    return props.scenes.data.map((scene: any) => {
        const uniqueDeviceMap = new Map((scene.scene_devices || []).map((device: any) => [device.name, device]));
        return {
            ...scene,
            uniqueDevices: Array.from(uniqueDeviceMap.values()),
            triggerDisplay: formatScheduleDisplay(scene, {
                manual: t('page.scene_management.label.manual'),
                everyday: t('page.scene_management.label.everyday'),
                scheduled: t('page.scene_management.label.scheduled'),
            }),
        };
    });
});

function reloadTable() {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}

const { heights, setContentRef } = useResizeObserverMap();

const modals = {
    update: useModal<any>(),
    delete: useModal<any>(),
};

const {
    update: { visible: showUpdateModal, data: showUpdateModalData, close: closeUpdateModal },
    delete: { visible: showDeleteModal, data: showDeleteModalData, close: closeDeleteModal },
} = modals;
</script>
<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(device, index) in tableData" :key="index">
                        <TableRow class="table-header-row">
                            <TableHead class="h-12 cursor-default py-3 lg:w-1/20">{{ $t('table.header.num') }}</TableHead>

                            <SortableTableHead column="scene_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.scene_name') }}
                            </SortableTableHead>

                            <SortableTableHead column="room_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>

                            <TableHead
                                class="flex h-12 cursor-default items-center py-3"
                                :style="isTablet || isMobile || isSmallMobile ? { height: 25 + heights[device.id] + 'px' } : {}"
                                >{{ $t('table.header.device_involved') }}</TableHead
                            >

                            <TableHead class="h-12 cursor-default py-3">{{ $t('table.header.trigger') }}</TableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-1/20"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody
                    v-if="processedScenes.length > 0"
                    class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0"
                >
                    <template v-for="(device, index) in processedScenes" :key="device.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 truncate py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.room?.name || '-' }}</TableCell>
                            <TableCell class="py-3">
                                <template v-if="device.uniqueDevices.length">
                                    <div :ref="setContentRef(device.id)" class="flex w-full flex-wrap items-center gap-1.5">
                                        <span
                                            v-for="sceneDevice in device.uniqueDevices.slice(0, 3)"
                                            :key="device.id + '-' + sceneDevice.id"
                                            :class="getDefaultBadgeClass(sceneDevice.name)"
                                            class="rounded-sm px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ sceneDevice.name }}
                                        </span>
                                        <template v-if="device.uniqueDevices.length > 3">
                                            <Popover v-model:open="expandedSceneDevices[device.id]">
                                                <PopoverTrigger as-child>
                                                    <button class="text-primary ml-1 text-xs underline hover:cursor-pointer">
                                                        {{
                                                            expandedSceneDevices[device.id]
                                                                ? 'Show less'
                                                                : '+' + (device.uniqueDevices.length - 3) + ' more'
                                                        }}
                                                    </button>
                                                </PopoverTrigger>
                                                <PopoverContent
                                                    class="bg-sidebar-background fluid-text-xs-sm flex w-auto max-w-[210px] min-w-[80px] flex-wrap gap-1.5 rounded border p-2 shadow"
                                                    align="center"
                                                    side="bottom"
                                                    :side-offset="8"
                                                >
                                                    <span
                                                        v-for="sceneDevice in device.uniqueDevices"
                                                        :key="device.id + '-' + sceneDevice.id + '-expanded'"
                                                        class="rounded-sm px-2.5 py-0.5 text-xs font-medium"
                                                        :class="getDefaultBadgeClass(sceneDevice.name)"
                                                    >
                                                        {{ sceneDevice.name }}
                                                    </span>
                                                </PopoverContent>
                                            </Popover>
                                        </template>
                                    </div>
                                </template>
                                <div v-else class="flex h-full items-center">
                                    <span>-</span>
                                </div>
                            </TableCell>

                            <TableCell class="h-12 truncate py-3">
                                <div class="flex items-center gap-2">
                                    <Icon :icon="device.triggerDisplay.icon" class="size-4 shrink-0 text-gray-500" />
                                    <span>{{ device.triggerDisplay.text }}</span>
                                </div>
                            </TableCell>

                            <TableCell class="h-12">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.update_scene' },
                                        delete: { label: 'modal.action.delete_scene' },
                                    }"
                                    @update="modals.update.open(device)"
                                    @delete="modals.delete.open(device)"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
                <TableBody v-else>
                    <TableEmpty :colspan="6">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>

    <!--Update Scene Modals -->
    <ScenePanel v-if="showUpdateModal" v-model:open="showUpdateModal">
        <template #content>
            <SceneEditPanel v-if="showUpdateModalData" :scene="showUpdateModalData" :rooms="rooms" @close="closeUpdateModal" :admin="true" />
        </template>
    </ScenePanel>

    <!-- Delete Scene Modals -->
    <SceneDeletePanel :scene="showDeleteModalData!" :open="showDeleteModal" @close="closeDeleteModal" />
</template>
