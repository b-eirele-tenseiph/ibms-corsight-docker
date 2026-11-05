<script lang="ts" setup>
import { ScenesHeaderSection, ScenesTable } from '@/components/admin/scenes';
import { Paginate } from '@/components/shared/pagination';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { SettingsLayout } from '@/layouts/settings';
import { ScenePageProps } from '@/types';
import { Head } from '@inertiajs/vue3';
import { provide } from 'vue';
const props = defineProps<ScenePageProps>();
provide('theme', props.theme);
provide('icon', props.icon);
</script>

<template>
    <Head :title="$t('head.scene_management')" />
    <AdminLayout title="head.scene_management">
        <SettingsLayout>
            <!-- Scene Header -->
            <ScenesHeaderSection :rooms :filters />

            <!-- Scene Table -->
            <ScenesTable :scenes :filters :rooms />

            <!-- Pagination -->
            <Paginate :paginate="scenes" />
        </SettingsLayout>
    </AdminLayout>
</template>
