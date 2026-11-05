<script setup lang="ts">
import { PlugSearch, PlugTable } from '@/components/admin/monitoring-plug';
import { Paginate } from '@/components/shared/pagination';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { PlugProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<PlugProps>();
</script>

<template>
    <Head :title="$t('head.plug_monitoring')" />
    <AdminLayout title="head.plug_monitoring">
        <AdminPageLayout>
            <PlugSearch :query-params="queryParams" />
            <PlugTable :appliances="appliances.data" :query-params="queryParams" />
            <Paginate :paginate="appliances" />
        </AdminPageLayout>
    </AdminLayout>
</template>
