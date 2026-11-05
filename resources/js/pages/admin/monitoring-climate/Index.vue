<script setup lang="ts">
import { ClimateSearch, ClimateTable } from '@/components/admin/monitoring-climate';
import { Paginate } from '@/components/shared/pagination';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { AirconProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<AirconProps>();
</script>

<template>
    <Head title="Climate Monitoring" />
    <AdminLayout title="head.climate_monitoring">
        <AdminPageLayout>
            <div class="xs:flex-row flex w-full flex-col gap-2">
                <ClimateSearch :filters="queryParams" />
            </div>
            <ClimateTable :query-params="queryParams" :aircons="aircons.data" />
            <Paginate :paginate="aircons" />
        </AdminPageLayout>
    </AdminLayout>
</template>
