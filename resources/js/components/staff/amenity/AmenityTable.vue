<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { getCategoryBadgeClass } from '@/lib/badge-colors';
import { index } from '@/routes/staff/amenity_management';
import { Amenity } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { AmenitySwitch } from '.';

const props = defineProps<{
    amenities: Record<string, any>;
    queryParams: object;
    onEdit: (amenity: Amenity) => void;
    onDelete: (amenity: Amenity) => void;
    onRead: (amenity: Amenity) => void;
}>();

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
    return props.amenities.data > 0 || isSmallScreen ? props.amenities.data : [{}];
});
</script>
<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="amenity in tableData" :key="amenity.id">
                        <TableRow class="bg-table-header-background table-header-row">
                            <SortableTableHead column="title" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.name') }}
                            </SortableTableHead>
                            <TableHead :colspan="4" class="h-24 py-3 lg:h-12">
                                {{ $t('table.header.description') }}
                            </TableHead>
                            <SortableTableHead :colspan="2" column="category_id" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.category') }}
                            </SortableTableHead>
                            <TableHead :colspan="1" class="h-12 py-3 text-start lg:text-center"> {{ $t('table.header.visibility') }}</TableHead>
                            <TableHead class="h-12 py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <!-- Table body: render user data rows if any exist -->
                <TableBody class="table-body [&_tr:last-child]:border lg:[&_tr:last-child]:border-0" v-if="props.amenities.data.length > 0">
                    <template v-for="amenity in props.amenities.data as Amenity[]" :key="amenity.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3 break-words whitespace-normal">{{ amenity.title ?? '-' }}</TableCell>
                            <TableCell :colspan="4" class="3xl:py-12 h-24 py-3 break-words whitespace-normal lg:h-12 lg:py-6 xl:py-8">
                                <p class="line-clamp-2 lg:line-clamp-3">{{ amenity.description }}</p>
                                <Button
                                    variant="link"
                                    class="text-primary-link-text fluid-text-xxs-sm p-0 font-semibold underline"
                                    @click="onRead(amenity)"
                                >
                                    {{ $t('action.details') }}
                                </Button>
                            </TableCell>
                            <TableCell class="h-12 py-3 break-words whitespace-normal" :colspan="2">
                                <Badge :class="getCategoryBadgeClass(amenity.amenity_category?.name ?? '')">
                                    {{ amenity.amenity_category?.name }}
                                </Badge>
                            </TableCell>
                            <TableCell :colspan="1" class="h-12 py-3 text-start lg:text-center">
                                <AmenitySwitch :amenity-id="amenity.id" :checked="amenity.is_visible" />
                            </TableCell>
                            <TableCell class="h-12 py-3 break-words whitespace-normal">
                                <div class="flex h-full items-center justify-start lg:justify-center">
                                    <DropdownAction
                                        class="h-full"
                                        :actions="{
                                            update: { label: 'modal.action.update_amenity' },
                                            delete: { label: 'modal.action.delete_amenity' },
                                        }"
                                        @update="props.onEdit(amenity)"
                                        @delete="props.onDelete(amenity)"
                                    >
                                    </DropdownAction>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <!-- Fallback UI when no users are available -->
                <TableBody v-else>
                    <TableEmpty :colspan="9">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
