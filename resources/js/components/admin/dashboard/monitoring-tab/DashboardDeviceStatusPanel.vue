<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { deviceStatus as routeDeviceStatus } from '@/routes/admin/dashboard';
import { DeviceStatus } from '@/types';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { onMounted, ref } from 'vue';

dayjs.extend(relativeTime);

const deviceStatus = ref<Array<DeviceStatus>>([]);
const isLoading = ref<boolean>(true);

// Load device data from backend
const loadData = () => {
    isLoading.value = true;
    axios
        .get(routeDeviceStatus.url())
        .then((response) => {
            deviceStatus.value = response.data.status;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

onMounted(() => {
    loadData();
});
</script>

<template>
    <Card class="h-full gap-4 py-4">
        <div class="flex w-full flex-col px-4">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.device_status') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{ $t('page.admin_dashboard.header.device_status_subtitle') }}</span>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <div v-if="deviceStatus?.length > 0" class="h-full overflow-hidden">
                <ScrollArea class="h-full px-4">
                    <div
                        v-for="(device, index) in deviceStatus"
                        :key="index"
                        class="flex items-center justify-between gap-2 border-b py-2 last:border-0 last:pb-0"
                    >
                        <div class="flex w-full flex-col">
                            <span class="fluid-text-xs-sm font-medium">{{ device.name }}</span>
                            <span class="text-secondary-text text-xs font-medium">
                                {{ $t('page.admin_dashboard.label.last_seen') }}: {{ dayjs().to(dayjs(device.last_seen)) }}</span
                            >
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <Icon :icon="device.icon" class="text-destructive size-4" />
                            <span class="text-destructive fluid-text-xs-sm font-medium">{{ device.status }}</span>
                        </div>
                    </div>
                </ScrollArea>
            </div>
            <div v-else class="py-10">
                <StateEmpty />
            </div>
        </template>
    </Card>
</template>
