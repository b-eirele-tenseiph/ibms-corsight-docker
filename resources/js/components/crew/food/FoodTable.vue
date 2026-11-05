<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { index, patch } from '@/routes/crew/foods';
import type { Food } from '@/types/t-food';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ComponentPublicInstance, computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
const props = defineProps<{ rows: Food[] }>();
const filters = defineModel<Record<string, any>>('filters');

defineEmits<{
    (e: 'update:filters', value: Record<string, any>): void;
    (e: 'update', food: Food): void;
    (e: 'delete', food: Food): void;
}>();

const busyId = ref<string | null>(null);
const { isExtraSmallMobile, isTablet, isMobile, isSmallMobile, isDesktop, isLaptop } = useBreakpoints();
const isSmallScreen = computed(() => isTablet.value || isMobile.value || isSmallMobile.value);
const tableData = computed(() => (props.rows.length > 0 || isSmallScreen.value ? props.rows : ([{}] as any)));
const heights = reactive<Record<number, number>>({});
const contentRefs = ref<Record<number, HTMLElement | null>>({});
const observers: Record<number, ResizeObserver> = {};
function setContentRef(id: number) {
    return (el: Element | ComponentPublicInstance | null) => {
        const htmlEl = el instanceof HTMLElement ? el : el && '$el' in el ? ((el as any).$el as HTMLElement) : null;
        contentRefs.value[id] = htmlEl;
        if (htmlEl) {
            if (observers[id]) observers[id].disconnect();
            observers[id] = new ResizeObserver((entries) => {
                for (const entry of entries) if (entry.contentRect) heights[id] = Math.round(entry.contentRect.height);
            });
            observers[id].observe(htmlEl);
        }
    };
}
onBeforeUnmount(() => {
    Object.values(observers).forEach((o) => o.disconnect());
});
// description helpers
const getDescription = (description: string) => {
    const words = description.split(' ');
    if (words.length > 30) return { short: words.slice(0, 20).join(' ') + '...', full: description };
    return { short: description, full: description };
};
const expanded = ref<Record<number, boolean>>({});
const toggleDescription = (id: number) => (expanded.value[id] = !expanded.value[id]);
/** Trigger sorting here (table owns it) */
watch(
    () => [filters.value?.sort, filters.value?.direction, filters.value?.page],
    ([sort, direction, page], [prevSort, prevDirection, prevPage]) => {
        if (!filters.value) return;
        if (sort === prevSort && direction === prevDirection && page === prevPage) return;
        const allowed = ['name', 'price'];
        const safeSort = allowed.includes(String(filters.value.sort)) ? filters.value.sort : 'name';
        const safeDir = String(filters.value.direction) === 'desc' ? 'desc' : 'asc';
        router.get(
            index.url(),
            { ...filters.value, sort: safeSort, direction: safeDir },
            {
                preserveScroll: true,
                replace: true,
            },
        );
    },
    { deep: false },
);
async function onToggle(food: Food, nextVal: boolean) {
    if (busyId.value === food.id) return;
    const prev = food.available;
    (food as any).available = nextVal;
    busyId.value = food.id;
    try {
        await axios.patch(patch({ food: food.id }).url, { available: nextVal }, { headers: { Accept: 'application/json' } });
    } catch {
        (food as any).available = prev;
    } finally {
        busyId.value = null;
    }
}
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm bg-table-header-background">
                    <template v-for="(food, index) in tableData" :key="food.id ?? index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <SortableTableHead
                                v-model:filters="filters"
                                column="name"
                                class="h-12 place-content-center py-3 select-none lg:w-[55%]"
                                :style="isTablet || isMobile || isSmallMobile ? { height: 17 + heights[(food as Food).id] + 'px' } : {}"
                            >
                                {{ $t('table.header.item') }}
                            </SortableTableHead>
                            <SortableTableHead v-model:filters="filters" column="price" class="h-12 py-3 select-none lg:w-[15%]">
                                {{ $t('table.header.price') }}</SortableTableHead
                            >

                            <TableHead class="h-12 w-[15%] py-3"> {{ $t('table.header.availability') }}</TableHead>
                            <TableHead class="h-12 w-[15%] py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <!-- Body -->
                <TableBody v-if="props.rows.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="food in props.rows" :key="food.id">
                        <TableRow class="table-body-row flex flex-col lg:table-row">
                            <TableCell
                                :ref="setContentRef(food.id)"
                                :class="[isLaptop || isDesktop ? 'w-[420px]' : 'w-full', '!h-auto overflow-visible py-2 align-top']"
                            >
                                <div class="flex items-center gap-4">
                                    <img :src="food.image" alt="Food Image" class="max-h-30 rounded-sm object-cover" />
                                    <div class="flex flex-col gap-y-2">
                                        <span class="fluid-text-xs-sm font-medium">{{ food.name }}</span>
                                        <div class="flex flex-col">
                                            <span v-if="expanded[food.id]" class="text-secondary-text text-xs whitespace-pre-line">
                                                {{ food.description }}
                                            </span>
                                            <span v-else class="text-secondary-text text-xs whitespace-pre-line">
                                                {{ getDescription(food.description).short }}
                                            </span>

                                            <button
                                                v-if="getDescription(food.description).full !== getDescription(food.description).short"
                                                class="text-primary-link-text mt-2 cursor-pointer self-start text-xs"
                                                @click="toggleDescription(food.id)"
                                            >
                                                {{ expanded[food.id] ? $t('action.show_less') : $t('action.show_more') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="h-12 py-3">{{ '₱' + food.price }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <div class="flex flex-row place-items-center items-center gap-2">
                                    <Switch
                                        :model-value="food.available"
                                        size="base"
                                        :disabled="busyId === food.id"
                                        @update:modelValue="(val) => onToggle(food, val)"
                                    />
                                </div>
                            </TableCell>
                            <TableCell class="h-12 py-3">
                                <DropdownAction
                                    :actions="{ update: { label: 'modal.action.edit_menu' }, delete: { label: 'modal.action.delete_menu' } }"
                                    @update="$emit('update', food)"
                                    @delete="$emit('delete', food)"
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
