<script setup lang="ts">
import { Action, ConfigurationModal, Filters, Scan, Table, UpdateModal } from '@/components/admin/switch-bot-remote';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { SwitchBotRemoteData, SwitchBotRemoteProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<SwitchBotRemoteProps>();

const updateModal = useModal();
const configureModal = useModal<SwitchBotRemoteData>();
</script>

<template>
    <Head :title="$t('head.switch_bot_remote')" />
    <AdminLayout title="head.switch_bot_remote">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-2">
                <div class="xs:flex-row flex w-full flex-col gap-2">
                    <Filters :filters :rooms />
                </div>
                <Scan class="flex flex-row justify-end gap-2" />
                <Action />
            </div>

            <Table :filters :remotes @update="updateModal.open" @configure="configureModal.open" />

            <Paginate :paginate="remotes" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Modal -->
    <UpdateModal v-if="updateModal.data.value" :show="updateModal.visible.value" :remote="updateModal.data.value" :rooms @close="updateModal.close" />

    <ConfigurationModal
        v-if="configureModal.data.value"
        :isOpen="configureModal.visible.value"
        :device="configureModal.data.value!"
        @close="configureModal.close"
    />
</template>
