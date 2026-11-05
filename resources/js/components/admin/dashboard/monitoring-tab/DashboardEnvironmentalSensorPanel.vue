<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { environmentSensorsData } from '@/routes/admin/dashboard';
import { SeriesItem } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { EnvironmentChartCard } from '..';

const isLoading = ref<boolean>(true);

const environmentData = ref({
    carbon_dioxideData: {
        params: { min: 400, max: 1200 },
        series: [] as SeriesItem[],
    },
    humidityData: {
        params: { min: 40, max: 60 },
        series: [] as SeriesItem[],
    },
    temperatureData: {
        params: { min: 15, max: 35 },
        series: [] as SeriesItem[],
    },
    illuminanceData: {
        params: { min: 2, max: 1100 },
        series: [] as SeriesItem[],
    },
});

const hasDataEnvironment = computed(() => {
    return Object.values(environmentData.value).some((d) => Array.isArray(d.series) && d.series.length > 0);
});

// load data
const loadData = () => {
    isLoading.value = true;

    axios
        .get(environmentSensorsData.url())
        .then((response) => {
            const {
                co2Data = { series: [] },
                humidityData = { series: [] },
                temperatureData = { series: [] },
                illuminanceData = { series: [] },
            } = response.data.sensors ?? {};

            environmentData.value.carbon_dioxideData.series = co2Data.series;
            environmentData.value.humidityData.series = humidityData.series;
            environmentData.value.temperatureData.series = temperatureData.series;
            environmentData.value.illuminanceData.series = illuminanceData.series;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const { stopListening: stopDevice, listen: listenDevice } = useEcho('admin.lorawan_device', '.lorawan_device_data.new', (data: any) => {
    // Process sensors data
    const sensorData = data.co2_temp_humid ?? data.occu_temp_light ?? [];
    sensorData.forEach((item: any) => {
        const type = item.type as 'carbon_dioxide' | 'humidity' | 'temperature' | 'illuminance';
        const targetSeries = environmentData.value[`${type}Data`]?.series.find((s: any) => s.id == item.device_id) ?? null;

        if (targetSeries) {
            targetSeries.data.push([item.received_at, item.context.value]);
            targetSeries.data.shift();
        }
    });
});

onMounted(() => {
    loadData();

    // Sensors data listener
    listenDevice();
});

onUnmounted(() => {
    stopDevice();
});
</script>

<template>
    <Card class="h-full gap-4 py-4">
        <div class="flex w-full flex-col px-4">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.environment_record') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold"
                >{{ $t('page.admin_dashboard.header.environment_record_subtitle') }}
            </span>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <div v-if="hasDataEnvironment" class="h-full overflow-hidden">
                <ScrollArea class="h-full px-4">
                    <!-- CO2 -->
                    <template v-for="(series, sIndex) in environmentData.carbon_dioxideData.series" :key="'co2-series-' + sIndex">
                        <div class="pb-4">
                            <EnvironmentChartCard
                                :seriesData="series"
                                :threshold="environmentData.carbon_dioxideData.params"
                                title="device.telemetry.co2"
                                unit="PPM"
                            />
                        </div>
                    </template>

                    <!-- Humidity -->
                    <template v-for="(series, sIndex) in environmentData.humidityData.series" :key="'humid-series-' + sIndex">
                        <div class="pb-4">
                            <EnvironmentChartCard
                                :seriesData="series"
                                :threshold="environmentData.humidityData.params"
                                title="device.telemetry.humidity"
                                unit="%"
                            />
                        </div>
                    </template>

                    <!-- Temperature -->
                    <template v-for="(series, sIndex) in environmentData.temperatureData.series" :key="'temp-series-' + sIndex">
                        <div class="pb-4">
                            <EnvironmentChartCard
                                :seriesData="series"
                                :threshold="environmentData.temperatureData.params"
                                title="device.telemetry.temperature"
                                unit="°C"
                            />
                        </div>
                    </template>

                    <!-- Illuminance -->
                    <template v-for="(series, sIndex) in environmentData.illuminanceData.series" :key="'illum-series-' + sIndex">
                        <div class="pb-4">
                            <EnvironmentChartCard
                                :seriesData="series"
                                :threshold="environmentData.illuminanceData.params"
                                title="device.telemetry.illuminance"
                                unit="LX"
                            />
                        </div>
                    </template>
                </ScrollArea>
            </div>
            <div v-else class="py-10">
                <StateEmpty />
            </div>
        </template>
    </Card>
</template>
