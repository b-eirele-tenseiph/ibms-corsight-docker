<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { DatePicker } from '.';

const props = defineProps<{
    data: Record<string, any>;
}>();

const filters = props.data;

// Debounced search logic
const search = reactive({
    query: computed<string | null>({
        get: () => filters.search ?? null,
        set: (newValue) => {
            filters.search = newValue || undefined;
        },
    }),
    timeout: null as any,
    load: () => {
        clearTimeout(search.timeout);
        search.timeout = setTimeout(reloadWithFilters, 500);
    },
}) as any;

// Trigger request with current filters
function reloadWithFilters() {
    router.get(
        window.location.pathname,
        { ...filters, page: 1 },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

// When a date is selected from calendar
const fetchRoomStatusForDate = (date: string) => {
    filters.date = date;
    reloadWithFilters();
};

// Legend display
const legendItems = [
    { label: 'vacant', color: '#10b981' },
    { label: 'check_in', color: '#1e88e5' },
    { label: 'occupied', color: '#FF6384' },
    { label: 'check_out', color: '#f97316' },
];
</script>

<template>
    <div class="flex w-full flex-col gap-2 lg:flex-row">
        <div class="xs:flex-row flex flex-col gap-3">
            <InputSearch v-model="search.query" @input="search.load" />
            <div class="xs:max-w-60 max-w-50">
                <DatePicker :modelValue="filters.date" @date-selected="fetchRoomStatusForDate" />
            </div>
        </div>
        <div class="flex flex-wrap gap-1 lg:ml-auto lg:gap-2">
            <div v-for="item in legendItems" :key="item.label" class="flex flex-row items-center gap-1">
                <Icon icon="material-symbols:circle" width="24" height="24" :style="{ color: item.color, opacity: 0.25 }" />
                <span class="text-secondary-text fluid-text-xs-sm">
                    {{ $t('page.staff_dashboard.label.' + item.label) }}
                </span>
            </div>
        </div>
    </div>
</template>
