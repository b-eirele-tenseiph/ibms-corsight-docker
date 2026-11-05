<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import Badge from '@/components/ui/badge/Badge.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { useResizeObserverMap } from '@/composables/useResizeObserverMap';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import { index } from '@/routes/admin/management/rooms';
import { Room } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

// Props passed to the component
const props = defineProps<{
    rooms: Room[];
    queryParams?: object;
}>();

// Tracks which room area tags are expanded
const expandedRoomAreas = ref<Record<number, boolean>>({});

// Breakpoint utility for responsive behavior
const { isExtraSmallMobile, isTablet, isMobile, isSmallMobile } = useBreakpoints();

// Custom composable for tracking dynamic content heights via ResizeObserver
const { heights, setContentRef } = useResizeObserverMap();

// Used to track client-side rendering (to avoid SSR mismatches)
const isClient = ref(false);

// Computed for smaller screen
const isSmallScreen = computed(() => isTablet.value || isMobile.value || isSmallMobile.value);

const tableData = computed(() => {
    // If data exists or it's a small screen (don't force a dummy)
    return props.rooms.length > 0 || isSmallScreen.value ? props.rooms : [{}];
});

// Set true on mount to confirm client rendering
onMounted(() => {
    isClient.value = true;
});

// Filters state derived from props
const filters = ref({ ...props.queryParams });

// Triggers a reload of the table using the current filters
function reloadTable() {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}

// Automatically reload table data when filters change
watch(filters, () => reloadTable(), { deep: true });
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(room, index) in tableData" :key="room.id ?? index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead class="h-12 py-3"> {{ $t('table.header.num') }} </TableHead>
                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="number" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_number') }}
                            </SortableTableHead>
                            <SortableTableHead column="floor" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.floor_name') }}
                            </SortableTableHead>
                            <TableHead
                                class="flex h-12 items-center py-3"
                                :style="isTablet || isMobile || isSmallMobile ? { height: 17 + heights[room.id] + 'px' } : {}"
                                >{{ $t('table.header.room_area') }}</TableHead
                            >
                            <TableHead class="h-12" />
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="props.rooms.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(room, index) in props.rooms" :key="room.id">
                        <TableRow class="table-body-row flex flex-col lg:table-row">
                            <TableCell class="h-12 py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ room.name }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ room.number }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ room.floor?.name ?? '-' }}</TableCell>
                            <TableCell>
                                <template v-if="Array.isArray(room.room_area) && room.room_area.length">
                                    <div :ref="setContentRef(room.id)" class="flex w-full flex-wrap gap-1.5">
                                        <Badge
                                            v-for="area in room.room_area.slice(0, 3)"
                                            :key="room.id + '-' + area"
                                            :class="getDefaultBadgeClass(area.id)"
                                            class="font-semibold"
                                        >
                                            {{ area.name }}
                                        </Badge>

                                        <template v-if="room.room_area.length > 3">
                                            <Popover v-model:open="expandedRoomAreas[room.id]">
                                                <PopoverTrigger as-child>
                                                    <button class="text-primary ml-1 text-xs underline hover:cursor-pointer">
                                                        {{
                                                            expandedRoomAreas[room.id]
                                                                ? $t('action.show_less')
                                                                : `+${room.room_area.length - 3} ${$t('action.more')}`
                                                        }}
                                                    </button>
                                                </PopoverTrigger>
                                                <PopoverContent
                                                    class="bg-sidebar-background fluid-text-xs-sm flex w-auto max-w-[210px] min-w-[80px] flex-wrap gap-1.5 rounded border p-2 shadow"
                                                    align="start"
                                                    side="bottom"
                                                    :side-offset="8"
                                                >
                                                    <Badge
                                                        v-for="area in room.room_area"
                                                        :key="room.id + '-' + area + '-expanded'"
                                                        class="font-semibold"
                                                        :class="getDefaultBadgeClass(area.id)"
                                                    >
                                                        {{ area.name }}
                                                    </Badge>
                                                </PopoverContent>
                                            </Popover>
                                        </template>
                                    </div>
                                </template>
                                <div v-else class="flex h-[31px] items-center">
                                    <span>—</span>
                                </div>
                            </TableCell>
                            <TableCell class="h-12 lg:text-center">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.update_room' },
                                        delete: { label: 'modal.action.delete_room' },
                                    }"
                                    @update="$emit('update', room)"
                                    @delete="$emit('delete', room)"
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
