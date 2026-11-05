<script lang="ts" setup>
import { TableHead } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const { isLaptop, isDesktop } = useBreakpoints();

const props = defineProps<{
    column: string;
}>();

const filters = defineModel<Record<string, any>>('filters');

// Check if current active sort is the same as column
const isSorted = computed(() => filters.value!.sort === props.column);

// Get icon name according to sorting status and direction
const iconName = computed(() => {
    if (!isSorted.value) return 'iconoir:sort';
    return filters.value!.direction === 'asc' ? 'iconoir:sort-up' : 'iconoir:sort-down';
});

// function for toggling sorting
function toggleSort(): void {
    filters.value = {
        ...filters.value,
        sort: props.column,
        direction: isSorted.value ? (filters.value!.direction === 'asc' ? 'desc' : 'asc') : 'asc',
        page: 1,
    };
}
</script>
<template>
    <TableHead v-if="isLaptop || isDesktop" class="h-12 cursor-pointer py-3 select-none" @click="toggleSort">
        <slot />
        <Icon :icon="iconName" :class="['ml-1 inline size-4 transition-opacity duration-200', isSorted ? 'opacity-100' : 'opacity-30']" />
    </TableHead>

    <TableHead v-else class="h-12 py-3">
        <slot />
    </TableHead>
</template>
