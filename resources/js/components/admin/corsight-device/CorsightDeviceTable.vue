<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { CorsightDeviceData, PaginatedResponse, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    devices: PaginatedResponse<CorsightDeviceData>;
    filters: QueryParams;
}>();

const filters = ref({ ...props.filters });

const roleBadgeVariants: Record<string, BadgeVariants['variant']> = {
    entry_point: 'success',
    exit_point: 'destructive',
    passage: 'alert',
    general: 'secondary',
};

// null = no badge (renders the "-" placeholder instead)
const healthVariant = (health?: CorsightDeviceData['health'] | null): BadgeVariants['variant'] | null => {
    if (!health) return null;
    if (health.stale) return 'destructive';
    if (health.status === 1) return 'success';
    if (health.status !== null) return 'secondary';
    return null;
};

const { isExtraSmallMobile } = useBreakpoints();

const load = () => {
    router.reload({ data: filters.value });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.devices.data.length > 0 || isSmallScreen.value ? props.devices.data : [{}];
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(device, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead class="h-12 py-3 lg:w-[5%]">{{ $t('table.header.num') }}</TableHead>
                            <TableHead class="h-12 py-3 lg:w-[14%]">{{ $t('table.header.status') }}</TableHead>
                            <SortableTableHead class="lg:w-[22%]" column="name" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.device_name') }}
                            </SortableTableHead>
                            <SortableTableHead class="lg:w-[14%]" column="room_name" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead class="lg:w-[14%]" column="room_area" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>
                            <TableHead class="h-12 py-3 lg:w-[10%]">{{ $t('table.header.role') }}</TableHead>
                            <TableHead class="h-12 lg:w-[11%]" />
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="devices.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(device, index) in devices.data" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 truncate py-3">{{ (devices.current_page - 1) * devices.per_page + index + 1 }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <Badge v-if="healthVariant(device.health)" :variant="healthVariant(device.health)" class="fluid-text-xxs-xs">
                                    {{
                                        device.health?.stale
                                            ? $t('page.corsight_device.status.inactive')
                                            : device.health?.status_str || device.health?.status
                                    }}
                                </Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device?.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device?.room?.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ device?.room_area?.name || '-' }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <Badge :variant="roleBadgeVariants[device.role] ?? 'secondary'" class="fluid-text-xxs-xs">
                                    {{ $t(`page.corsight_person.role.${device.role}`) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="h-12">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.edit_device' },
                                        configure: { label: 'modal.action.configurations' },
                                        delete: { label: 'modal.action.delete_device' },
                                    }"
                                    @update="$emit('update', device)"
                                    @configure="$emit('configure', device)"
                                    @delete="$emit('delete', device)"
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
