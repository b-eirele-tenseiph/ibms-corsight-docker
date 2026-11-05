<script setup lang="ts">
import { LogsHeaderSection, LogsTable } from '@/components/admin/logs';
import { Paginate } from '@/components/shared/pagination';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type Paginate as PaginateType } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    logs: Record<string, any>;
    filter: any;
    queryParams: object;
}>();
</script>

<template>
    <Head :title="$t('head.logs')" />
    <AdminLayout title="head.logs">
        <AdminPageLayout>
            <!-- Header Section-->
            <LogsHeaderSection :filter="filter" :queryParams="queryParams" />

            <!-- Table -->
            <LogsTable :logs="logs" :queryParams="queryParams" />

            <!-- Pagination -->
            <Paginate :paginate="props.logs as PaginateType" />
        </AdminPageLayout>
    </AdminLayout>
</template>
