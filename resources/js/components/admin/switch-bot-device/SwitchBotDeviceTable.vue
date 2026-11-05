<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { PaginatedResponse, QueryParams, SwitchBotDeviceData } from '@/types';
import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { useMediaQuery } from '@vueuse/core';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { SwitchBotDeviceBattery } from '.';

const props = defineProps<{
    devices: PaginatedResponse<SwitchBotDeviceData>;
    filters: QueryParams;
}>();

const { isExtraSmallMobile } = useBreakpoints();

// Filters state derived from props
const filters = ref(props.filters);

// Triggers a reload of the table using the current filters
const reloadTable = () => {
    router.reload({ data: filters.value });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.devices.data.length > 0 || isSmallScreen.value ? props.devices.data : [{}];
});

const reactiveAppliances = ref([...props.devices.data]);

const { stopListening: stopDevice, listen: listenDevice } = useEcho(`admin.switch_bot_device`, '.switch_bot_device_data.new', (data: any) => {
    Object.values(data).forEach((eventData: any) => {
        const applianceToUpdate = reactiveAppliances.value.find((appliance: any) => appliance.id === eventData.device_id);

        if (applianceToUpdate) {
            applianceToUpdate.latest_data = eventData.context;
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
                    <template v-for="(device, index) in tableData" :key="index">
                        <TableRow class="table-header-row">
                            <TableHead class="h-12 cursor-default py-3 lg:w-[5%]">{{ $t('table.header.num') }}</TableHead>

                            <SortableTableHead column="room_name" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>

                            <SortableTableHead column="room_area" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>

                            <SortableTableHead column="name" class="lg:w-[20%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.device_name') }}
                            </SortableTableHead>

                            <SortableTableHead column="type_name" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.device_type') }}
                            </SortableTableHead>

                            <SortableTableHead column="serial_number" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.serial_num') }}
                            </SortableTableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-[10%]">
                                {{ $t('table.header.battery_level') }}
                            </TableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-[5%]"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="devices.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(device, index) in devices.data" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device?.room?.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.room_area?.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.type.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.serial_number }}</TableCell>
                            <TableCell class="h-12 truncate py-3">
                                <SwitchBotDeviceBattery :type="device.type.name" :status="device.latest_data?.battery" />
                            </TableCell>
                            <TableCell class="h-12">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.edit_device' },
                                        configure: { label: 'modal.action.configurations' },
                                    }"
                                    @update="$emit('update', device)"
                                    @configure="$emit('configure', device)"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
                <TableBody v-else>
                    <TableEmpty :colspan="8">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
