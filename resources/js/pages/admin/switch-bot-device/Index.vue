<script lang="ts" setup>
import {
    SwitchBotDeviceConfigurationModal,
    SwitchBotDeviceHeader,
    SwitchBotDeviceTable,
    SwitchBotDeviceUpdateModal,
} from '@/components/admin/switch-bot-device';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { SwitchBotDeviceData, SwitchBotDeviceProps } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<SwitchBotDeviceProps>();

const params = ref({ ...props.filters });

const updateModal = useModal();
const configureModal = useModal<SwitchBotDeviceData>();
</script>
<template>
    <Head :title="$t('head.switch_bot_device')" />
    <AdminLayout title="head.switch_bot_device">
        <AdminPageLayout>
            <!-- Header Section -->
            <SwitchBotDeviceHeader :query="params" :rooms />

            <!-- Table -->
            <SwitchBotDeviceTable :filters :devices @update="updateModal.open" @configure="configureModal.open" />

            <!-- Pagination -->
            <Paginate :paginate="devices" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Modal -->
    <SwitchBotDeviceUpdateModal
        v-if="updateModal.data.value"
        :show="updateModal.visible.value"
        :device="updateModal.data.value"
        @close="updateModal.close"
        :rooms
    />

    <SwitchBotDeviceConfigurationModal
        v-if="configureModal.data.value"
        :isOpen="configureModal.visible.value"
        :device="configureModal.data.value!"
        @close="configureModal.close"
    />
</template>
