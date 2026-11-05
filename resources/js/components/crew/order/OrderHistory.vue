<script setup lang="ts">
import SortableTableHead from '@/components/shared/sortables/SortableTableHead.vue';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetClose, SheetContent, SheetHeader, SheetTrigger } from '@/components/ui/sheet';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { getOrderStatusBadgeClass } from '@/lib/badge-colors';
import { formatReadableDate } from '@/lib/utils';
import { history } from '@/routes/crew/orders';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

// Props passed to the component
const props = defineProps<{
    orders: any;
    queryParams?: object;
}>();

// Breakpoint utility for responsive behavior
const { isExtraSmallMobile, isTablet, isMobile, isSmallMobile } = useBreakpoints();

// Used to track client-side rendering (to avoid SSR mismatches)
const isClient = ref(false);

// Computed for smaller screen
const isSmallScreen = computed(() => isTablet.value || isMobile.value || isSmallMobile.value);

const tableData = computed(() => {
    // If data exists or it's a small screen (don't force a dummy)
    return props.orders.length > 0 || isSmallScreen.value ? props.orders : [{}];
});

// Filters state derived from props
const filters = ref({ ...props.queryParams });

// Set true on mount to confirm client rendering
onMounted(() => {
    isClient.value = true;
});

// Triggers a reload of the table using the current filters
function reloadTable() {
    router.get(history.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}

// Automatically reload table data when filters change
watch(filters, () => reloadTable(), { deep: true });

const expandedItems = ref(new Set<number>());

const toggleDescription = (id: number) => {
    if (expandedItems.value.has(id)) {
        expandedItems.value.delete(id);
    } else {
        expandedItems.value.add(id);
    }
};
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(room, index) in tableData" :key="room.id ?? index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <SortableTableHead
                                @update:filters="reloadTable"
                                v-model:filters="filters"
                                column="id"
                                class="h-12 py-3 select-none lg:w-[15%]"
                            >
                                {{ $t('table.header.num') }}</SortableTableHead
                            >
                            <!-- <TableHead class="h-12 py-3"> {{ $t('table.header.room_name') }} </TableHead> -->
                            <SortableTableHead
                                @update:filters="reloadTable"
                                v-model:filters="filters"
                                column="room_name"
                                class="h-12 py-3 select-none lg:w-[15%]"
                            >
                                {{ $t('table.header.room_name') }}</SortableTableHead
                            >

                            <SortableTableHead
                                @update:filters="reloadTable"
                                v-model:filters="filters"
                                column="user"
                                class="h-12 py-3 select-none lg:w-[15%]"
                            >
                                {{ $t('table.header.user') }}</SortableTableHead
                            >

                            <SortableTableHead
                                @update:filters="reloadTable"
                                v-model:filters="filters"
                                column="status"
                                class="h-12 py-3 select-none lg:w-[15%]"
                            >
                                {{ $t('table.header.status') }}</SortableTableHead
                            >
                            <TableHead class="h-12 py-3"> {{ $t('table.header.date') }} </TableHead>

                            <TableHead class="h-12" />
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="props.orders.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="order in props.orders" :key="order.id">
                        <TableRow class="table-body-row flex flex-col lg:table-row">
                            <TableCell class="h-12 py-3">{{ order.id }}</TableCell>
                            <TableCell class="h-12 py-3">{{ order.room?.name ?? '-' }}</TableCell>
                            <TableCell class="h-12 py-3">{{ order.user?.name ?? '-' }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <Badge :class="[getOrderStatusBadgeClass(order.status.toLocaleLowerCase())]" class="font-semibold capitalize">
                                    {{ order.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="h-12 py-3">{{ formatReadableDate(order.created_at) }}</TableCell>
                            <TableCell class="h-12 py-3 text-center">
                                <Sheet>
                                    <SheetTrigger class="text-info-text cursor-pointer underline"> {{ $t('action.details') }}</SheetTrigger>
                                    <SheetContent
                                        :showClose="false"
                                        class="3xl:min-w-[700px] bg-card w-full gap-y-0 rounded-tl-[8px] rounded-bl-[8px] sm:min-w-[500px] xl:min-w-[600px]"
                                    >
                                        <div>
                                            <SheetHeader class="flex flex-row justify-between">
                                                <SheetClose as-child class="cursor-pointer">
                                                    <Icon icon="lucide:arrow-right-to-line" class="text-secondary-text h-5.5 w-5.5" />
                                                </SheetClose>
                                            </SheetHeader>

                                            <hr class="border-secondary-light-gray" />
                                        </div>

                                        <div class="space-y-2 p-6">
                                            <!-- Order number -->
                                            <p class="text-lg font-semibold">{{ $t('page.order_history.label.order_no') }}{{ order.id }}</p>

                                            <!-- Status and date -->
                                            <div class="fluid-text-xs-sm flex justify-between">
                                                <span class="flex items-center gap-2">
                                                    <p class="font-bold">{{ $t('page.order_history.label.status') }}:</p>
                                                    <Badge
                                                        :class="[getOrderStatusBadgeClass(order.status.toLocaleLowerCase())]"
                                                        class="font-semibold capitalize"
                                                    >
                                                        {{ order.status }}
                                                    </Badge>
                                                </span>
                                                <span>{{ formatReadableDate(order.created_at) }}</span>
                                            </div>

                                            <!-- Room and User -->
                                            <div class="fluid-text-xs-sm space-y-2">
                                                <p>
                                                    <span class="font-bold">{{ $t('page.order_history.label.room_name') }}: </span>
                                                    {{ order.room?.name ?? '-' }}
                                                </p>
                                                <p>
                                                    <span class="font-bold">{{ $t('page.order_history.label.user') }}: </span>
                                                    {{ order.user?.name ?? '-' }}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-3 pt-4">
                                                <p class="text-secondary-text fluid-text-xs-sm font-semibold whitespace-nowrap">
                                                    {{ $t('page.order_history.label.order_details') }}
                                                </p>
                                                <hr class="border-secondary-light-gray flex-1 border-t" />
                                            </div>

                                            <div class="pt-4">
                                                <ul class="space-y-4">
                                                    <li v-for="detail in order.details" :key="detail.id" class="flex items-center gap-3">
                                                        <img :src="detail.food.image" alt="Food Image" class="size-20 rounded-full object-cover" />
                                                        <div class="flex-1">
                                                            <p class="fluid-text-xs-base font-semibold">{{ detail.food.name }}</p>
                                                            <p
                                                                :class="[
                                                                    'fluid-text-xs-sm text-gray-500 transition-all duration-300',
                                                                    expandedItems.has(detail.id) ? '' : 'line-clamp-2',
                                                                ]"
                                                            >
                                                                {{ detail.food.description }}
                                                            </p>
                                                            <button
                                                                v-if="detail.food.description && detail.food.description.length > 60"
                                                                @click="toggleDescription(detail.id)"
                                                                class="mt-1 w-fit cursor-pointer text-[10px] font-bold tracking-wider text-blue-600 uppercase hover:text-blue-800"
                                                            >
                                                                {{ expandedItems.has(detail.id) ? 'Show Less' : 'Show More' }}
                                                            </button>
                                                            <p class="fluid-text-xxs-xs text-gray-400">{{ detail.quantity }} item</p>
                                                        </div>
                                                        <span class="fluid-text-xs-sm"> ₱{{ detail.food.price }} </span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="fluid-text-xs-sm mt-4 flex justify-between border-t pt-4 font-bold">
                                                <span>{{ $t('page.order_history.label.total_amount') }}</span>
                                                <span>
                                                    ₱
                                                    {{ order.details.reduce((acc, d) => acc + d.quantity * d.food.price, 0).toFixed(2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </SheetContent>
                                </Sheet>
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
