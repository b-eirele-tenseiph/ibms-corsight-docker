<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { index } from '@/routes/admin/management/lorawan/devices';
import { LorawanDeviceData, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    queryParams: QueryParams;
    devices: Array<LorawanDeviceData>;
}>();

const { isExtraSmallMobile } = useBreakpoints();

const filters = ref({ ...props.queryParams });

const reloadTable = () => {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
};

const emit = defineEmits<{
    (e: 'update', device: any): void;
    (e: 'delete', device: any): void;
    (e: 'configure', device: any): void;
}>();

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.devices.length > 0 || isSmallScreen.value ? props.devices : [{}];
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(device, index) in tableData" :key="index">
                        <TableRow class="table-header-row">
                            <TableHead class="h-12 py-3 lg:w-[7%]">{{ $t('table.header.num') }}</TableHead>
                            <SortableTableHead column="room_name" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="room_area" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>
                            <SortableTableHead column="name" class="lg:w-[20%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.device_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="dev_eui" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.dev_eui') }}
                            </SortableTableHead>
                            <SortableTableHead column="model" class="lg:w-[10%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.model') }}
                            </SortableTableHead>
                            <SortableTableHead column="gateway_name" class="lg:w-[10%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.gateway') }}
                            </SortableTableHead>
                            <TableHead class="h-12 lg:w-[8%]"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <!-- Table Rows -->
                <TableBody v-if="devices.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(device, index) in devices" :key="device.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.room?.name || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.room_area?.name || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.name || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.dev_eui || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.model || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device.gateway?.ip_address || '—' }}</TableCell>
                            <TableCell class="h-12">
                                <DropdownAction
                                    class="text-primary-text"
                                    :actions="{
                                        update: { label: 'modal.action.edit_device' },
                                        delete: { label: 'modal.action.delete_device' },
                                        configure: { label: 'modal.action.configurations' },
                                    }"
                                    @update="() => emit('update', device)"
                                    @delete="() => emit('delete', device)"
                                    @configure="() => emit('configure', device)"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <!-- Empty State -->
                <TableBody v-else>
                    <TableEmpty :colspan="8">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
