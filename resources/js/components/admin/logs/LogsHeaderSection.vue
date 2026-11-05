<script setup lang="ts">
import { LogsExportButton, LogsFilterDropdown, LogsInputSearch } from '@/components/admin/logs';

defineProps<{
    filter: any;
    queryParams: object;
}>();
</script>

<template>
    <div class="flex flex-row justify-between sm:gap-4">
        <div class="flex w-full">
            <!-- Search Input -->
            <LogsInputSearch :queryParams="queryParams" />

            <!-- Filter Dropdown -->
            <LogsFilterDropdown :filter="filter" :queryParams="queryParams" />
        </div>

        <!-- Export Button -->
        <LogsExportButton :queryParams="queryParams" />
    </div>
</template>
