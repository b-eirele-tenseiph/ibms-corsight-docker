<script setup lang="ts">
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { useResizeObserverMap } from '@/composables/useResizeObserverMap';
import { index } from '@/routes/admin/monitoring-controls/switches';
import { LorawanSwitch, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { useMediaQuery } from '@vueuse/core';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { SwitchToggle } from '.';

const props = defineProps<{
    switches: Array<LorawanSwitch>;
    queryParams?: QueryParams;
}>();

const { isTablet, isMobile, isSmallMobile, isExtraSmallMobile } = useBreakpoints();

const { heights, setContentRef } = useResizeObserverMap();

// Reactive filters object initialized from query parameters
const filters = ref({ ...props.queryParams });

// Reloads the table with current filters
const reloadTable = () => {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.switches.length > 0 || isSmallScreen.value ? props.switches : [{}];
});

const { stopListening: stopDevice, listen: listenDevice } = useEcho('admin.lorawan_device', '.lorawan_device_data.new', (payload: any) => {
    payload.switch?.forEach((switchItem: any) => {
        const device = props.switches.find((x) => x.id === switchItem.device_id);
        if (device?.latest_data[switchItem.type]) {
            device.latest_data[switchItem.type] = {
                unit: switchItem.context.unit,
                value: switchItem.context.value,
            };
        }
    });
});

onMounted(() => {
    listenDevice();
});

onUnmounted(() => {
    stopDevice();
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(switchItem, index) in tableData" :key="switchItem.id ?? index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <SortableTableHead column="room_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="area" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>
                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.device_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="model" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.model') }}
                            </SortableTableHead>
                            <TableHead
                                class="h-12 place-content-center py-3 select-none lg:w-[30%]"
                                :style="
                                    isTablet || isMobile || isSmallMobile ? { height: 24 + heights[(switchItem as LorawanSwitch).id!] + 'px' } : {}
                                "
                            >
                                {{ $t('table.header.controls') }}
                            </TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="switches.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="switchItem in switches" :key="switchItem.id">
                        <TableRow class="table-body-row flex flex-col lg:table-row">
                            <TableCell class="h-12 truncate py-3">{{ switchItem.room?.name ?? '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchItem.room_area?.name ?? '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchItem.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchItem.model }}</TableCell>
                            <TableCell class="py-3">
                                <div :ref="setContentRef(switchItem.id)">
                                    <template v-if="Object.keys(switchItem?.latest_data ?? {}).length">
                                        <SwitchToggle :switchItem="switchItem" />
                                    </template>
                                    <template v-else>
                                        <p class="text-info fluid-text-xs-sm font-bold">
                                            {{ $t('error.connection_waiting') }}
                                        </p>
                                    </template>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <!-- Empty State -->
                <TableBody v-else>
                    <TableEmpty :colspan="5">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
