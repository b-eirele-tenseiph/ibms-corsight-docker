<script setup lang="ts">
import { SwitchSearch, SwitchTable } from '@/components/admin/monitoring-switch';
import { Paginate } from '@/components/shared/pagination';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { SwitchProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<SwitchProps>();
</script>

<template>
    <Head :title="$t('head.switch_monitoring')" />
    <AdminLayout title="head.switch_monitoring">
        <AdminPageLayout>
            <SwitchSearch :query-params="queryParams" />
            <SwitchTable :switches="switches.data" :query-params="queryParams" />
            <Paginate :paginate="switches" />
        </AdminPageLayout>
    </AdminLayout>
</template>
