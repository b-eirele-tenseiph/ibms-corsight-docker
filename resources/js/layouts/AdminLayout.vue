<script setup lang="ts">
import { EmergencyAdminModal } from '@/components/admin/emergency';
import { AdminSidebarLayout } from '@/layouts/admin';
import { PageTitle } from '@/types';

defineProps<PageTitle>();
</script>

<template>
    <AdminSidebarLayout :title="title">
        <slot />
    </AdminSidebarLayout>

    <EmergencyAdminModal />
</template>
