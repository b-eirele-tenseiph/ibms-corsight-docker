<script setup lang="ts">
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { useResizeObserverMap } from '@/composables/useResizeObserverMap';
import { getLogBadgeClass } from '@/lib/badge-colors';
import { errorUpdate, index } from '@/routes/admin/management/logs';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    logs: Record<string, any>;
    queryParams: object;
}>();

// Break Points
const { isTablet, isMobile, isSmallMobile, isExtraSmallMobile } = useBreakpoints();

// Dynamic Height
const { heights, setContentRef } = useResizeObserverMap();

const tableData = computed(() => {
    const isSmallScreen = isTablet.value || isMobile.value || isSmallMobile.value;
    return props.logs.data.length > 0 || isSmallScreen ? props.logs.data : [{}];
});

const filters = ref({ ...props.queryParams });

function reloadTable() {
    router.get(
        index.url(),
        {
            ...props.queryParams,
            ...filters.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

// Remove room_id from properties for display
const properties = (properties: any) => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { room_id: _, ...rest } = properties;
    return rest;
};

const downloadGenerateReport = (downloadUrl: string) => {
    window.location.href = downloadUrl;
};
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(log, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <SortableTableHead column="properties.code" class="lg:w-[10%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.code') }}
                            </SortableTableHead>

                            <SortableTableHead column="log_name" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.severity') }}
                            </SortableTableHead>

                            <SortableTableHead column="updated_at" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.timestamp') }}
                            </SortableTableHead>

                            <SortableTableHead column="causer_id" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.originated_by') }}
                            </SortableTableHead>

                            <SortableTableHead column="causer_type" class="lg:w-[15%]" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.originated_type') }}
                            </SortableTableHead>

                            <TableHead
                                class="h-12 place-content-center py-3 select-none lg:w-[30%]"
                                :style="
                                    logs.data.length > 0 && (isTablet || isMobile || isSmallMobile)
                                        ? { height: 28 + heights[logs.data[index].id] + 'px' }
                                        : {}
                                "
                            >
                                {{ $t('table.header.description') }}
                            </TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="logs.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <TableRow v-for="(log, index) in logs.data" :key="index" class="table-body-row">
                        <TableCell class="h-12 py-3">
                            <span class="flex h-full items-start lg:py-4">
                                {{ log.properties?.code || log.properties?.Code }}
                            </span>
                        </TableCell>
                        <TableCell class="h-12 py-3">
                            <span class="flex h-full items-start lg:py-4"> {{ log.log_name }}</span>
                        </TableCell>
                        <TableCell class="h-12 py-3">
                            <span class="flex h-full items-start lg:py-4">
                                {{ log.created_at ? new Date(log.created_at).toLocaleString() : '' }}
                            </span>
                        </TableCell>
                        <TableCell class="h-12 py-3">
                            <span class="flex h-full items-start lg:py-4">
                                {{ log.causer_name }}
                            </span>
                        </TableCell>
                        <TableCell class="h-12 cursor-pointer py-3">
                            <span class="flex h-full items-start lg:py-4">
                                <Badge :class="[getLogBadgeClass(log.causer_type?.toLocaleLowerCase() || 'system'), 'font-semibold']">
                                    {{ log.causer_type || 'System' }}
                                </Badge>
                            </span>
                        </TableCell>

                        <TableCell>
                            <div :ref="setContentRef(log.id)" class="w-full">
                                <Accordion type="single" collapsible>
                                    <AccordionItem :value="String(log.id)">
                                        <AccordionTrigger
                                            class="fluid-text-xs-sm h-12 cursor-pointer items-center py-8 break-words whitespace-pre-wrap"
                                            >{{ log.description }}
                                        </AccordionTrigger>
                                        <AccordionContent class="py-0">
                                            <div class="bg-table-header-background space-y-2 rounded-sm p-3">
                                                <div
                                                    v-for="(value, key) in properties(log.properties)"
                                                    :key="key"
                                                    class="fluid-text-xs-sm break-words whitespace-pre-wrap"
                                                >
                                                    <div v-if="value" class="flex">
                                                        <span>{{ key }}: </span>
                                                        <div class="font-bold">
                                                            <span v-if="key !== 'Link'">{{ value }}</span>
                                                            <span
                                                                v-else
                                                                class="fluid-text-xs-sm text-primary-link-text cursor-pointer hover:underline"
                                                                @click="downloadGenerateReport(value)"
                                                            >
                                                                {{ $t('action.download', { file_type: $t('label.report') }) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </AccordionContent>
                                        <!-- action -->
                                        <div class="mt-3">
                                            <Link
                                                :href="errorUpdate(log.id).url"
                                                v-if="!log.properties?.['Resolved by'] && log.log_name === 'error'"
                                                as="button"
                                                method="put"
                                                replace
                                                class="fluid-text-xs-sm text-primary-link-text cursor-pointer text-sm font-semibold"
                                            >
                                                {{ $t('action.mark_as_resolved') }}
                                            </Link>
                                        </div>
                                    </AccordionItem>
                                </Accordion>
                            </div>
                        </TableCell>
                    </TableRow>
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
