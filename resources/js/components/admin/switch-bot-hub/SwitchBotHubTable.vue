<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { PaginatedResponse, QueryParams, SwitchBotHubData } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    hubs: PaginatedResponse<SwitchBotHubData>;
    filters: QueryParams;
}>();

const { isExtraSmallMobile } = useBreakpoints();

const filters = ref({ ...props.filters });

// Triggers a reload of the table using the current filters
const reloadTable = () => {
    router.reload({ data: filters.value });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.hubs.data.length > 0 || isSmallScreen.value ? props.hubs.data : [{}];
});
</script>
<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(switchBotHub, index) in tableData" :key="index">
                        <TableRow class="table-header-row">
                            <TableHead class="hidden h-12 py-3 lg:table-cell"> {{ $t('table.header.num') }} </TableHead>
                            <SortableTableHead column="room_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="room_area" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>
                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.hub_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="serial_number" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.serial_num') }}
                            </SortableTableHead>
                            <TableHead class="h-12 py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody class="table-body [&_tr:last-child]:border lg:[&_tr:last-child]:border-0" v-if="props.hubs?.data.length > 0">
                    <template v-for="(switchBotHub, index) in hubs.data" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="hidden h-12 py-3 lg:table-cell">
                                {{ (props.hubs.current_page - 1) * props.hubs.per_page + index + 1 }}
                            </TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchBotHub.room?.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchBotHub.room_area?.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchBotHub.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ switchBotHub.serial_number }}</TableCell>
                            <TableCell class="h-12">
                                <div class="flex h-full justify-start">
                                    <DropdownAction
                                        class="h-full"
                                        :actions="{
                                            update: { label: 'modal.action.update_switch_bot_hub' },
                                        }"
                                        @update="$emit('onEdit', switchBotHub)"
                                    >
                                    </DropdownAction>
                                </div>
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
</template>
