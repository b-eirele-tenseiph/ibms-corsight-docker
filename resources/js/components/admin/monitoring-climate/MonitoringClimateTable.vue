<script setup lang="ts">
import { StateEmpty } from '@/components/shared/states';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { SortableTableHead } from '@/components/shared/sortables';
import { Badge } from '@/components/ui/badge';
import { useEcho } from '@laravel/echo-vue';
import { AirconData, QueryParams } from '@/types';
import { useMediaQuery } from '@vueuse/core';
import { AirconButton, ThermostatButton } from '.';
import { Label } from '@/components/ui/label';
import { useResizeObserverMap } from '@/composables/useResizeObserverMap';
import { ScrollArea } from '@/components/ui/scroll-area';
import { index } from '@/routes/admin/monitoring-controls/aircons';

const props = defineProps<{
    queryParams?: QueryParams;
    aircons: Array<AirconData>;
}>();

const { isTablet, isMobile, isSmallMobile, isExtraSmallMobile } = useBreakpoints();

const { heights, setContentRef } = useResizeObserverMap();

const filters = ref({ ...props.queryParams });

const reloadTable = () => {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
};

const color = (temp: number) => {
    if (temp >= 30) {
        return 'bg-red-100 text-red-600';
    } else if (temp >= 25) {
        return 'bg-orange-100 text-orange-600';
    } else if (temp >= 20) {
        return 'bg-yellow-100 text-yellow-600';
    } else {
        return 'bg-blue-100 text-blue-600';
    }
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.aircons.length > 0 || isSmallScreen.value ? props.aircons : [{}];
});

const { stopListening: stopDevice, listen: listenDevice } = useEcho('admin.switch_bot_remote', '.switch_bot_remote_data.new', (data) => {
    const events = [data.aircon].flat().filter(Boolean);

    events.forEach((event) => {
        const device = props.aircons.find((a) => a.id === event.device_id);
        if (device?.latest_data) {
            device.latest_data = event.context;
        }
    });
});

const { stopListening: stopRemote, listen: listenRemote } = useEcho('admin.lorawan_device', '.lorawan_device_data.new', (data: any) => {
    const events = [data.thermostat].flat().filter(Boolean);

    events.forEach((event: any) => {
        const device = props.aircons.find((aircon: any) => aircon.id === event.device_id);
        if (device?.latest_data) {
            device.latest_data[event.type] = event.context;
        }
    });
});

onMounted(() => {
    // Thermostat
    listenDevice();

    // SwitchBot Remote
    listenRemote();
});

onUnmounted(() => {
    stopDevice();
    stopRemote();
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(aircon, index) in tableData" :key="aircon.id ?? index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <SortableTableHead column="room_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="area" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_area') }}
                            </SortableTableHead>
                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.device_name') }}
                            </SortableTableHead>
                            <TableHead class="h-12 place-content-center py-3 select-none">
                                {{ $t('table.header.temperature') }}
                            </TableHead>
                            <TableHead
                                class="h-12 place-content-center py-3 select-none lg:w-[35%]"
                                :style="isTablet || isMobile || isSmallMobile ? { height: 24 + heights[(aircon as AirconData).id!] + 'px' } : {}"
                            >
                                {{ $t('table.header.controls') }}
                            </TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="aircons.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="aircon in aircons" :key="aircon.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 truncate py-3">
                                {{ (aircon as { room: { name: string } }).room?.name }}
                            </TableCell>

                            <TableCell class="h-12 truncate py-3">
                                {{ (aircon as any).room_area?.name ?? '—' }}
                            </TableCell>

                            <TableCell class="h-12 truncate py-3">
                                {{ (aircon as { name: string }).name }}
                            </TableCell>

                            <TableCell class="h-12 truncate">
                                <Badge
                                    v-if="aircon.latest_data?.temperature"
                                    :class="['w-14 py-1.5 font-semibold', color(aircon.latest_data?.temperature)]"
                                >
                                    <template v-if="aircon.model == 'UC300'">
                                        <span>{{
                                            Number(
                                                aircon.tempHasChanged
                                                    ? aircon.latest_data.current_setpoint.value
                                                    : aircon.latest_data.temperature.value,
                                            ).toFixed(1)
                                        }}</span>
                                    </template>
                                    <template v-else>
                                        <span>{{ Number(aircon.latest_data?.temperature).toFixed(1) }} </span>
                                    </template>
                                    <sup class="text-xxs">°C</sup>
                                </Badge>
                                <span v-else> — </span>
                            </TableCell>

                            <TableCell class="py-3">
                                <div :ref="setContentRef(aircon.id)">
                                    <template v-if="aircon.model == 'UC300' && aircon.latest_data">
                                        <ThermostatButton
                                            :thermostat="aircon"
                                            :data="aircon.latest_data"
                                            v-model:has-changed="aircon.tempHasChanged"
                                        />
                                    </template>
                                    <template v-else-if="aircon.model == 'Air Conditioner' && aircon.latest_data">
                                        <AirconButton :aircon="aircon" :data="aircon.latest_data" />
                                    </template>
                                    <template v-else>
                                        <Label class="text-info fluid-text-xs-sm font-bold">
                                            {{ $t('error.connection_waiting') }}
                                        </Label>
                                    </template>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <TableBody v-else>
                    <TableEmpty :colspan="5">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
