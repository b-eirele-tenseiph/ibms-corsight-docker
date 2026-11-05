<script setup lang="ts">
import DropdownAction from '@/components/shared/dropdowns/DropdownAction.vue';
import SortableTableHead from '@/components/shared/sortables/SortableTableHead.vue';
import StateEmpty from '@/components/shared/states/StateEmpty.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import TableEmpty from '@/components/ui/table/TableEmpty.vue';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { PaginatedResponse, QueryParams, SwitchBotRemoteData } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    filters: QueryParams;
    remotes: PaginatedResponse<SwitchBotRemoteData>;
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
    return props.remotes.data.length > 0 || isSmallScreen.value ? props.remotes.data : [{}];
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(remote, index) in tableData" :key="index">
                        <TableRow class="bg-accent/60 table-header-row">
                            <TableHead class="h-12 py-3 lg:w-[10%]">{{ $t('table.header.num') }}</TableHead>

                            <SortableTableHead column="room_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>

                            <SortableTableHead column="room_area" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>

                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.device_name') }}
                            </SortableTableHead>

                            <SortableTableHead column="serial_number" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.serial_num') }}
                            </SortableTableHead>
                            <TableHead class="h-12 w-[12%]" />
                        </TableRow>
                    </template>
                </TableHeader>

                <!-- Table Rows -->
                <TableBody v-if="remotes.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(remote, index) in remotes.data" :key="remote.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ remote.room?.name || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ remote.room_area?.name || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ remote.name || '—' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ remote.serial_number || '—' }}</TableCell>
                            <TableCell class="h-12">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.edit_device' },
                                        configure: { label: 'modal.action.configurations' },
                                    }"
                                    @update="$emit('update', remote)"
                                    @configure="$emit('configure', remote)"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <!-- Empty State -->
                <TableBody v-else>
                    <TableEmpty :colspan="6">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
