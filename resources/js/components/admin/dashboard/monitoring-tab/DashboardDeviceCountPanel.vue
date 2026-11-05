<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useRooms } from '@/composables/useRooms';
import { devicesCountData } from '@/routes/admin/dashboard';
import { index as routeGateway } from '@/routes/admin/management/lorawan/gateways';
import { index as routeRoom } from '@/routes/admin/management/rooms';
import { index as routeSwitchBot } from '@/routes/admin/management/switchbot/accounts';
import { DeviceInfo } from '@/types';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, reactive } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { totalActiveDevices, totalRooms, occupiedRoomsCount } = useRooms();

const apiData = reactive({
    accounts: { total: 0, param: 0 },
    gateways: { total: 0, param: 0 },
    devicesTotal: 0,
});

const data = computed(() => [
    {
        key: 'accounts',
        total: apiData.accounts.total,
        param: apiData.accounts.param,
    },
    {
        key: 'gateways',
        total: apiData.gateways.total,
    },
    {
        key: 'devices',
        total: apiData.devicesTotal,
        param: totalActiveDevices.value,
    },
    {
        key: 'rooms',
        total: totalRooms.value,
        param: occupiedRoomsCount.value,
    },
]);

// Load device data from backend
const loadData = () => {
    axios
        .get(devicesCountData.url())
        .then((response) => {
            const { accounts, gateways, devices } = response.data.count;
            apiData.accounts = { total: accounts?.total ?? 0, param: accounts?.param ?? 0 };
            apiData.gateways = { total: gateways?.total ?? 0, param: gateways?.param ?? 0 };
            apiData.devicesTotal = devices?.total ?? 0;
        })
        .catch(console.error);
};

const item = computed<Record<string, DeviceInfo>>(() => ({
    devices: {
        label: t('page.admin_dashboard.label.device'),
        icon: 'tabler:bulb',
        badge: t('page.admin_dashboard.label.active'),
    },
    accounts: {
        label: t('page.admin_dashboard.label.account'),
        icon: 'fluent:iot-16-regular',
        badge: t('page.admin_dashboard.label.sync'),
        link: routeSwitchBot.url(),
    },
    gateways: {
        label: t('page.admin_dashboard.label.gateway'),
        icon: 'lsicon:online-gateway-outline',
        link: routeGateway.url(),
    },
    rooms: {
        label: t('page.admin_dashboard.label.room'),
        icon: 'material-symbols:meeting-room-outline',
        badge: t('page.admin_dashboard.label.occupied'),
        link: routeRoom.url(),
    },
}));

onMounted(() => {
    loadData();
});
</script>
<template>
    <div class="grid h-full grid-cols-2 gap-2 min-[1366px]:grid-cols-4 min-[1900px]:grid-cols-4 lg:grid-cols-2 xl:grid-cols-2">
        <Card v-for="entry in data" :key="entry.key" class="justify-center gap-2 p-3">
            <div class="text-secondary-text flex flex-row items-center gap-1">
                <Icon :icon="item[entry.key]?.icon" class="size-4 shrink-0" />
                <span class="fluid-text-xs-sm w-full truncate overflow-hidden font-semibold whitespace-nowrap">
                    {{ item[entry.key]?.label }}
                </span>

                <DropdownMenu v-if="item[entry.key]?.link">
                    <DropdownMenuTrigger class="cursor-pointer"><Icon icon="lucide:ellipsis" /></DropdownMenuTrigger>
                    <DropdownMenuContent class="min-w-fit" align="end">
                        <DropdownMenuItem class="fluid-text-xs-sm">
                            <Link :href="item[entry.key].link"> {{ $t('filter.view_all') }} </Link></DropdownMenuItem
                        >
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div class="flex flex-row items-center gap-4">
                <span class="text-2xl font-bold">{{ entry.total || 0 }}</span>
                <Badge v-if="entry.param !== undefined" variant="secondary" class="fluid-text-xs-sm font-semibold">
                    {{ entry.param }} {{ item[entry.key]?.badge }}
                </Badge>
            </div>
        </Card>
    </div>
</template>
