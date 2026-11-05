<script setup lang="ts">
import { ElectricMonitoringHeader, ElectricMonitoringOverview } from '@/components/admin/electric-monitoring';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { ElectricMeterProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<ElectricMeterProps>();
</script>

<template>
    <Head :title="$t('head.electric_monitoring')" />
    <AdminLayout title="head.electric_monitoring">
        <AdminPageLayout class="gap-2">
            <!-- Metrics Header -->
            <ElectricMonitoringHeader :electric_meter :filters />

            <!-- Metrics Overview -->
            <ElectricMonitoringOverview :electric_meter :filters />
        </AdminPageLayout>
    </AdminLayout>
</template>
