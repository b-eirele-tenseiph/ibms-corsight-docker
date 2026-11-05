<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { index } from '@/routes/staff/promo_management';
import type { Promo, PromoTableProps } from '@/types/t-promo';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineEmits(['onView']);

const props = defineProps<PromoTableProps>();

// Responsive breakpoints to control layout visibility
const { isExtraSmallMobile, isSmallMobile, isMobile, isTablet } = useBreakpoints();

// Initialize filters from query params (for sorting/filtering)
const filters = ref({ ...props.queryParams });

// Trigger a page reload with updated filters (used by sortable headers)
function reloadTable() {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}

// Computed data for table: show placeholder row if screen is small or no data exists
const tableData = computed(() => {
    const isSmallScreen = isTablet.value || isMobile.value || isSmallMobile.value;
    return props.promos.data.length > 0 || isSmallScreen ? props.promos.data : [{}];
});
</script>
<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(promo, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead :colspan="2" class="h-12 py-3"></TableHead>
                            <SortableTableHead :colspan="2" column="title" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.title') }}
                            </SortableTableHead>
                            <TableHead :colspan="4" class="h-12 py-3">
                                {{ $t('table.header.description') }}
                            </TableHead>
                            <SortableTableHead :colspan="2" column="valid_start_date" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.validity_period') }}
                            </SortableTableHead>
                            <TableHead class="h-12 py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <!-- Table body: render promo data rows if any exist -->
                <TableBody class="table-body [&_tr:last-child]:border lg:[&_tr:last-child]:border-0" v-if="props.promos.data.length > 0">
                    <template v-for="(promo, index) in props.promos.data as Promo[]" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell :colspan="2" class="h-12 py-3">
                                <div class="flex h-full items-center justify-start lg:justify-center">
                                    <img
                                        class="h-25 w-40 cursor-pointer rounded-md object-cover object-top sm:h-30"
                                        :src="`${promo.photo_file_path}`"
                                        @click="$emit('onView', promo)"
                                    />
                                </div>
                            </TableCell>
                            <TableCell :colspan="2" class="h-12 py-3">{{ promo.title }}</TableCell>
                            <TableCell :colspan="4" class="3xl:py-12 h-24 py-3 break-words whitespace-normal lg:h-12 lg:py-6 xl:py-8">
                                <p class="line-clamp-2 normal-case lg:line-clamp-3">
                                    {{ promo.description }}
                                </p>
                            </TableCell>
                            <TableCell :colspan="2" class="h-12 py-3">
                                {{
                                    new Date(promo.valid_start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                                }}
                                -
                                {{ new Date(promo.valid_end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                            </TableCell>
                            <TableCell class="h-12 py-3 break-words whitespace-normal">
                                <div class="flex h-full items-center justify-start lg:justify-center">
                                    <DropdownAction
                                        class="h-full"
                                        :actions="{
                                            update: { label: 'modal.action.update_promo' },
                                            delete: { label: 'modal.action.delete_promo' },
                                        }"
                                        @update="props.onEdit(promo)"
                                        @delete="props.onDelete(promo)"
                                    >
                                    </DropdownAction>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <!-- Fallback UI when no promos are available -->
                <TableBody v-else>
                    <TableEmpty :colspan="11">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
