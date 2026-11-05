<script setup lang="ts">
import { TableHead } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps<{
    data: Record<string, any>;
    header: { label: string; by: string };
}>();

const { isLaptop, isDesktop } = useBreakpoints();

const filters = props.data;

const sort = reactive({
    sortBy: computed<string | null>({
        get: () => filters.by ?? null,
        set: (newValue) => {
            filters.by = newValue || undefined;
        },
    }),
    sortOrder: computed<string | null>({
        get: () => filters.order ?? null,
        set: (newValue) => {
            filters.order = newValue || undefined;
        },
    }),
    load: (column: any) => {
        // Toggle order if clicking same column
        sort.sortOrder = sort.sortBy === column && sort.sortOrder === 'asc' ? 'desc' : 'asc';
        sort.sortBy = column;

        // Force reset to page 1 on sort
        router.reload({
            data: {
                ...filters,
                page: 1,
            },
        });
    },
}) as any;
</script>

<template>
    <TableHead class="h-12 cursor-pointer py-3 select-none" @click="sort.load(header.by)" v-if="isLaptop || isDesktop">
        <div class="flex flex-row items-center gap-2">
            {{ header.label }}
            <Icon
                :icon="sort.sortBy === header.by ? (sort.sortOrder === 'asc' ? 'iconoir:sort-up' : 'iconoir:sort-down') : 'iconoir:sort'"
                class="size-4 transition-transform duration-200"
                :style="{
                    color:
                        sort.sortBy === header.by
                            ? sort.sortOrder === 'asc' || sort.sortOrder === 'desc'
                                ? 'black'
                                : 'currentColor'
                            : 'currentColor',
                    opacity: sort.sortBy === header.by ? 1 : 0.5,
                }"
            />
        </div>
    </TableHead>

    <TableHead v-else class="h-12 py-3"> {{ header.label }}</TableHead>
</template>
