<script setup lang="ts">
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { CorsightPersonSessionData, PaginatedResponse, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    people: PaginatedResponse<CorsightPersonSessionData>;
    filters: QueryParams;
}>();

// Emits events to parent component (used for viewing the session detail)
const emit = defineEmits(['view']);

const { isExtraSmallMobile } = useBreakpoints();

// Filters state derived from props
const filters = ref(props.filters);

// Triggers a reload of the table using the current filters
const load = () => {
    router.reload({ data: filters.value });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.people.data.length > 0 || isSmallScreen.value ? props.people.data : [{}];
});

const formatDwell = (seconds: number | null) => {
    if (seconds === null) return '-';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    if (hours > 0) return `${hours}h ${minutes}m`;
    if (minutes > 0) return `${minutes}m ${secs}s`;
    return `${secs}s`;
};
</script>
<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(people, index) in tableData" :key="index">
                        <TableRow class="table-header-row">
                            <TableHead class="h-12 cursor-default py-3 lg:w-[5%]">{{ $t('table.header.num') }}</TableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-[7%]">{{ $t('table.header.photo') }}</TableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-[13%]">{{ $t('table.header.camera') }}</TableHead>
                            <TableHead class="h-12 cursor-default py-3 lg:w-[12%]">{{ $t('table.header.room_name') }}</TableHead>

                            <SortableTableHead column="enter_at" class="lg:w-[13%]" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.enter_at') }}
                            </SortableTableHead>

                            <SortableTableHead column="exit_at" class="lg:w-[13%]" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.exit_at') }}
                            </SortableTableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-[9%]">{{ $t('table.header.dwell') }}</TableHead>
                            <TableHead class="h-12 cursor-default py-3 lg:w-[7%]">{{ $t('table.header.gender') }}</TableHead>
                            <TableHead class="h-12 cursor-default py-3 lg:w-[8%]">{{ $t('table.header.age_group') }}</TableHead>

                            <TableHead class="h-12 cursor-default py-3 lg:w-[10%]">{{ $t('table.header.security') }}</TableHead>
                            <TableHead class="h-12 cursor-default py-3 lg:w-[9%]">{{ $t('table.header.status') }}</TableHead>
                            <TableHead class="h-12 lg:w-[10%]" />
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="people.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(person, index) in people.data" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 truncate py-3"> {{ (people.current_page - 1) * people.per_page + index + 1 }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <img
                                    v-if="person.image"
                                    :src="`data:image/jpeg;base64,${person.image}`"
                                    :alt="$t('table.header.photo')"
                                    class="size-9 rounded-md object-cover"
                                />
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="h-12 truncate py-3">{{ person.camera || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ person.room?.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ person.enter_at || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ person.exit_at || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ formatDwell(person.dwell_seconds) }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ person.gender || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ person.age_group || '-' }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <Badge v-if="person.poi_name" variant="destructive" class="fluid-text-xxs-xs">
                                    {{ person.poi_name }}
                                </Badge>
                                <Badge v-else-if="person.security_flag" variant="secondary" class="fluid-text-xxs-xs">
                                    {{ $t('page.corsight_person.label.watchlist_hit') }}
                                </Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="h-12 py-3">
                                <Badge v-if="person.status === 'in'" variant="success" class="fluid-text-xxs-xs">
                                    {{ $t('page.corsight_person.status.in') }}
                                </Badge>
                                <Badge v-else-if="person.status === 'exited'" variant="destructive" class="fluid-text-xxs-xs">
                                    {{ $t('page.corsight_person.status.exited') }}
                                </Badge>
                                <Badge v-else variant="outline" class="fluid-text-xxs-xs">
                                    {{ $t('page.corsight_person.status.abandoned') }}
                                </Badge>
                            </TableCell>
                            <TableCell class="h-12 py-3">
                                <Button size="xs" class="fluid-text-xs-sm h-full cursor-pointer rounded-md px-3" @click="$emit('view', person)">
                                    {{ $t('action.show') }}
                                </Button>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
                <TableBody v-else>
                    <TableEmpty :colspan="12">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
