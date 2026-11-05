<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { index } from '@/routes/admin/management/logs';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { parseDate } from '@internationalized/date';
import { DateRange } from 'reka-ui';
import { computed, ref, watch } from 'vue';
import { LogsDateRangePicker } from '.';

// Interface for filtering logs.
interface QueryParams {
    severity?: string;
    origin_type?: string;
    date_from?: string | null;
    date_to?: string | null;
    [key: string]: any;
}

const props = defineProps<{
    filter?: any[];
    queryParams: QueryParams;
}>();

// Reactive filter states
const search = ref(props.queryParams?.search || '');
const severity = ref(props.queryParams?.severity || '');
const originType = ref(props.queryParams?.origin_type || '');
const dateRangeOpen = ref(false);
const dateRange = ref<DateRange>({
    start: parseDate(props.queryParams.date_from as string),
    end: parseDate(props.queryParams.date_to as string),
});

// formatDate
function formatDate(date?: any) {
    return date ? date.toString() : null;
}

// Watch for severity changes in props
watch(
    () => props.queryParams?.origin_type,
    (newVal) => {
        originType.value = newVal || '';
    },
);

// Compute active filters count
const activeFilterCount = computed(() => {
    let count = 0;
    if (severity.value) count++;
    if (originType.value) count++;
    if (dateRange.value.start && dateRange.value.end) count++;
    return count;
});

// Filter Function
function applyFilters() {
    router.get(
        index.url(),
        {
            ...props.queryParams,
            ...(severity.value ? { severity: severity.value } : {}),
            ...(originType.value ? { origin_type: originType.value } : {}),
            ...(dateRange.value.start ? { date_from: formatDate(dateRange.value.start) } : {}),
            ...(dateRange.value.end ? { date_to: formatDate(dateRange.value.end) } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

// Reset function
function resetFilters() {
    severity.value = '';
    originType.value = '';
    search.value = props.queryParams?.search;

    router.get(
        index.url(),
        {
            ...(search.value ? { search: search.value } : {}),
        },
        {
            preserveState: false,
            preserveScroll: true,
            replace: true,
        },
    );
}

// Avoid duplicate values
const uniqueSeverities = computed(() => {
    const seen = new Set();
    return (props.filter ?? []).reduce((acc: any[], log: any) => {
        if (!seen.has(log.log_name)) {
            seen.add(log.log_name);
            acc.push({ ...log, name: log.log_name });
        }
        return acc;
    }, []);
});

const uniqueOriginType = computed(() => {
    const seen = new Set();
    return Array.from(
        (props.filter ?? []).filter((log) => log.causer_type),
        (log) => {
            if (!seen.has(log.causer_type)) {
                seen.add(log.causer_type);
                return { ...log, name: log.causer_type };
            }
            return null;
        },
    ).filter(Boolean);
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger>
            <Button variant="outline" class="relative mr-2 ml-4 flex items-center gap-4">
                <div class="xs:flex hidden w-full items-center gap-2">
                    <span class="xs:inline fluid-text-xs-sm hidden">{{ $t('operation.filter') }}</span>
                    <Badge
                        v-if="activeFilterCount > 0"
                        variant="secondary"
                        class="bg-primary text-primary-foreground fluid-text-xxs-xs size-4.5 items-center justify-center rounded-full"
                    >
                        {{ activeFilterCount }}
                    </Badge>
                </div>
                <Icon icon="heroicons-outline:filter" width="16" height="16" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="mr-8 w-50 space-y-2 p-2 px-4 lg:w-60 lg:space-y-4" align="start">
            <h3 class="fluid-text-xs-sm font-medium">{{ $t('page.activity_log.label.filter') }}</h3>

            <!-- Severity Select -->
            <div class="w-full">
                <label class="fluid-text-xxs-xs mb-1 block">{{ $t('page.activity_log.label.severity') }}</label>
                <SelectArea
                    :items="uniqueSeverities"
                    v-model="severity"
                    valueKey="log_name"
                    itemKey="log_name"
                    placeholder="filter.select_severity"
                />
            </div>

            <!-- Origin Type Select -->
            <div class="w-full">
                <label class="fluid-text-xxs-xs mb-1 block">{{ $t('page.activity_log.label.origin_type') }}</label>
                <SelectArea
                    :items="uniqueOriginType"
                    v-model="originType"
                    valueKey="causer_type"
                    itemKey="causer_type"
                    placeholder="filter.select_origin_type"
                />
            </div>

            <!-- Date Range Section (stubbed) -->
            <div>
                <div class="w-full">
                    <button
                        type="button"
                        class="mb-2 flex w-full cursor-pointer items-center space-x-1 text-sm font-medium focus:outline-none"
                        @click="dateRangeOpen = !dateRangeOpen"
                    >
                        <span class="fluid-text-xxs-xs">{{ $t('page.activity_log.label.date_range') }}</span>
                        <Icon
                            :icon="'lucide:chevron-down'"
                            width="16"
                            height="16"
                            :class="dateRangeOpen ? 'rotate-180 transform transition-transform' : 'transition-transform'"
                        />
                    </button>
                    <div v-show="dateRangeOpen" class="flex space-x-2">
                        <LogsDateRangePicker v-model="dateRange" />
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end">
                <Button variant="link" class="text-primary-link-text cursor fluid-text-xs-sm cursor-pointer px-1" @click="resetFilters">{{
                    $t('action.reset')
                }}</Button>
                <Button variant="link" class="text-primary-link-text fluid-text-xs-sm cursor-pointer px-1" @click.prevent="applyFilters">{{
                    $t('action.apply')
                }}</Button>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
