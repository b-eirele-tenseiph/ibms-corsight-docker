<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { getGatewayConnectionStatusBadgeClass } from '@/lib/badge-colors';
import { index } from '@/routes/admin/management/lorawan/gateways';
import { LorawanGateway, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    gateways: Array<LorawanGateway>;
    queryParams?: QueryParams;
}>();

const { isExtraSmallMobile } = useBreakpoints();

const filters = ref({ ...props.queryParams });

const reloadTable = () => {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.gateways.length > 0 || isSmallScreen.value ? props.gateways : [{}];
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(gateway, index) in tableData" :key="gateway.id ?? index">
                        <TableRow class="table-header-row">
                            <TableHead class="h-12 py-3">{{ $t('table.header.num') }}</TableHead>
                            <SortableTableHead column="floor" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.floor_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="ip_address" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.ip_address') }}
                            </SortableTableHead>
                            <SortableTableHead column="mac_address" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.mac_address') }}
                            </SortableTableHead>
                            <SortableTableHead column="connection" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.connection') }}
                            </SortableTableHead>
                            <TableHead class="h-12 py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody
                    v-if="props.gateways.length > 0"
                    class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0"
                >
                    <template v-for="(gateway, index) in props.gateways" :key="gateway.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ gateway.floor?.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ gateway.ip_address }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ gateway.mac_address }}</TableCell>
                            <TableCell class="h-12 truncate py-3">
                                <Badge :class="['font-semibold', getGatewayConnectionStatusBadgeClass(gateway.connection.toLocaleLowerCase())]">{{
                                    gateway.connection
                                }}</Badge>
                            </TableCell>
                            <TableCell class="h-12 lg:text-center">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.update_gateway' },
                                        delete: { label: 'modal.action.delete_gateway' },
                                    }"
                                    @update="$emit('update', gateway)"
                                    @delete="$emit('delete', gateway)"
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
</template>
