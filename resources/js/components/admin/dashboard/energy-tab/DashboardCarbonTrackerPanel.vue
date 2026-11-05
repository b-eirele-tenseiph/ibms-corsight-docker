<script setup lang="ts">
import { deviceCarbonFootprintData } from '@/routes/admin/dashboard';
import { CarbonFootPrintData, ElectricMeterData } from '@/types';
import axios from 'axios';
import { ref, watch } from 'vue';
import { CarbonTrackerContent } from '..';

const props = defineProps<{
    meter: Array<ElectricMeterData>;
}>();

const carbonFootprint = ref<CarbonFootPrintData | null>(null);

const isLoading = ref<boolean>(true);

const loadData = () => {
    axios
        .get(deviceCarbonFootprintData.url(), {
            params: {
                device_ids: props.meter.map((m: any) => m.id),
            },
        })
        .then((response) => {
            carbonFootprint.value = response.data.carbon;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

watch(
    () => props.meter,
    (newVal) => {
        if (newVal?.length) {
            loadData();
        } else {
            carbonFootprint.value = null;
            isLoading.value = false;
        }
    },
    { immediate: true },
);
</script>

<template>
    <CarbonTrackerContent :carbonFootprint="carbonFootprint" :meter="meter" :isLoading="isLoading" />
</template>
