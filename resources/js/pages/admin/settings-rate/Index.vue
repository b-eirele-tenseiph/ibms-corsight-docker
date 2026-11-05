<script setup lang="ts">
import { SettingsInfoSubHeader } from '@/components/admin/settings';
import {
    ElectricityRateMainPanel,
    WaterRateAddModal,
    WaterRateDeleteModal,
    WaterRateMainPanel,
    WaterRateUpdateModal,
} from '@/components/admin/settings-rate';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { useModal } from '@/composables/useModal';
import { AdminLayout } from '@/layouts/admin';
import { GeneralSettingsLayout, SettingsLayout } from '@/layouts/settings';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps<{
    consumer_types: Array<any>;
    electricity_latest_rate: { effective_date: string | null; rate_per_kwh: number };
    electricity_rates: Array<any>;
    water_latest_rates: Array<any>;
}>();

const modals = {
    addElectricityRate: useModal(),
    showElectricityRate: useModal(),

    addWaterRate: useModal(),
    updateWaterRate: useModal<any>(),
    deleteWaterRate: useModal<any>(),
};

const {
    addElectricityRate: { visible: showAddElectricityRateModal },
    showElectricityRate: { visible: showElectricityRateModal },

    addWaterRate: { visible: showWaterRateAddModal, open: openWaterRateAddModal, close: closeWaterRateAddModal },
    updateWaterRate: { visible: showWaterRateUpdateModal, open: openWaterRateUpdateModal, data: showWaterRateData, close: closeWaterRateUpdateModal },
    deleteWaterRate: {
        visible: showWaterRateDeleteModal,
        open: openWaterRateDeleteModal,
        data: showWaterRateDeleteModalData,
        close: closeWaterRateDeleteModal,
    },
} = modals;

const rates = computed(() => {
    return props.electricity_rates.map((rate) => ({
        ...rate,
        latest: rate.effective_date === props.electricity_latest_rate.effective_date,
    }));
});
</script>
<template>
    <AdminLayout title="head.general_settings">
        <SettingsLayout>
            <GeneralSettingsLayout>
                <div class="h-full overflow-hidden">
                    <ScrollArea class="h-full">
                        <div class="max-w-4xl space-y-12">
                            <div class="space-y-6">
                                <SettingsInfoSubHeader
                                    title="page.rate_settings.header.electricity"
                                    description="page.rate_settings.header.electricity_subtitle"
                                />

                                <div class="text-info-text flex items-center gap-2">
                                    <Icon icon="lucide-info" class="size-4 shrink-0" />
                                    <span class="fluid-text-xs-sm font-medium">{{ $t('modal.alert.rate_alert') }}</span>
                                </div>

                                <ElectricityRateMainPanel
                                    :showAddModal="showAddElectricityRateModal"
                                    :showViewRateModal="showElectricityRateModal"
                                    :rates="rates"
                                    :latest_rate="electricity_latest_rate"
                                />
                            </div>

                            <div class="space-y-6">
                                <SettingsInfoSubHeader
                                    title="page.rate_settings.header.water"
                                    description="page.rate_settings.header.water_subtitle"
                                />

                                <WaterRateMainPanel
                                    :latest_rate="water_latest_rates"
                                    @openAddModal="openWaterRateAddModal"
                                    @openUpdateModal="openWaterRateUpdateModal"
                                    @openDeleteModal="openWaterRateDeleteModal"
                                />
                            </div>
                        </div>
                    </ScrollArea>
                </div>
            </GeneralSettingsLayout>
        </SettingsLayout>
    </AdminLayout>

    <WaterRateAddModal :consumer_types :show="showWaterRateAddModal" @close="closeWaterRateAddModal" />

    <WaterRateUpdateModal
        v-if="showWaterRateData"
        :consumer_types
        :show="showWaterRateUpdateModal"
        :rate="showWaterRateData!"
        @close="closeWaterRateUpdateModal"
    />
    <WaterRateDeleteModal
        v-if="showWaterRateDeleteModalData"
        :show="showWaterRateDeleteModal"
        :rate="showWaterRateDeleteModalData!"
        @close="closeWaterRateDeleteModal"
    />
</template>
