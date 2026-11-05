<script setup lang="ts">
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { index } from '@/routes/admin/monitoring-controls/plugs';
import type { QueryParams, SwitchBotDeviceData } from '@/types';
import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { useMediaQuery } from '@vueuse/core';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { PlugToggle } from '.';

const props = defineProps<{
    appliances: Array<SwitchBotDeviceData>;
    queryParams?: QueryParams;
}>();

const { isExtraSmallMobile } = useBreakpoints();

const filters = ref<QueryParams>({
    ...(props.queryParams ?? {}),
});

// Reloads the table with current filters
function reloadTable() {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}

const reactiveAppliances = ref([...props.appliances]);

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.appliances.length > 0 || isSmallScreen.value ? props.appliances : [{}];
});

const { stopListening: stopDevice, listen: listenDevice } = useEcho(`admin.switch_bot_device`, '.switch_bot_device_data.new', (data: any) => {
    const device = reactiveAppliances.value.find((d: any) => d.id === data.device.device_id);
    if (device) {
        device.latest_data = {
            ...device.latest_data,
            ...data.device.context,
        };
    }
});

onMounted(() => {
    // Listen for the private channel and the specific event
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
                    <template v-for="(applianceItem, index) in tableData" :key="applianceItem.id ?? index">
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
                            <TableHead class="h-12 py-3"> {{ $t('table.header.type') }} </TableHead>
                            <TableHead class="h-12 py-3">{{ $t('table.header.controls') }}</TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody
                    v-if="reactiveAppliances.length > 0"
                    class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0"
                >
                    <template v-for="applianceItem in reactiveAppliances" :key="applianceItem.id">
                        <TableRow class="table-body-row flex flex-col lg:table-row">
                            <TableCell class="h-12 py-3">{{ applianceItem.room?.name ?? '—' }}</TableCell>
                            <TableCell class="h-12 py-3">{{ applianceItem.room_area?.name ?? '—' }}</TableCell>
                            <TableCell class="h-12 py-3">{{ applianceItem.name }}</TableCell>
                            <TableCell class="h-12 py-3">{{ applianceItem.type?.name }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <template v-if="Object.keys(applianceItem?.latest_data ?? {}).length">
                                    <PlugToggle :applianceItem="applianceItem" />
                                </template>
                                <template v-else>
                                    <p class="text-info fluid-text-xs-sm font-bold">
                                        {{ $t('error.connection_waiting') }}
                                    </p>
                                </template>
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
