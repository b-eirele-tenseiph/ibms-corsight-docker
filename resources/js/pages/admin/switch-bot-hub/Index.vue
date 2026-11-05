<script setup lang="ts">
import { SwitchBotHubFilters, SwitchBotHubSync, SwitchBotHubTable, SwitchBotHubUpdateModal } from '@/components/admin/switch-bot-hub';
import SwitchBotHubActions from '@/components/admin/switch-bot-hub/SwitchBotHubActions.vue';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import type { SwitchBotHubProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<SwitchBotHubProps>();

const UPDATE_MODAL = useModal();
</script>

<template>
    <Head :title="$t('head.switch_bot_hub')" />
    <AdminLayout title="head.switch_bot_hub">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-2">
                <SwitchBotHubFilters :filters :rooms />
                <SwitchBotHubSync />
                <SwitchBotHubActions />
            </div>

            <SwitchBotHubTable :hubs :filters @onEdit="UPDATE_MODAL.open" />

            <Paginate :paginate="hubs" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Edit Switch Bot Hub Modal Component -->
    <SwitchBotHubUpdateModal
        v-if="UPDATE_MODAL.data.value"
        :show="UPDATE_MODAL.visible.value"
        @close="UPDATE_MODAL.close"
        :hubs="UPDATE_MODAL.data.value"
        :rooms
    />
</template>
