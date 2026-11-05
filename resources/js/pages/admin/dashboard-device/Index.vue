<script setup lang="ts">
import { DashboardDevicesTab, DashboardGraphTab } from '@/components/admin/dashboard-device';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { AdminLayout } from '@/layouts/admin';
import { LorawanSwitch, PaginatedResponse, QueryParams, SwitchBotDeviceData, SwitchBotRemoteData } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    room: any;
    queryParams: QueryParams;
    activeTab: 'aircon' | 'switches' | 'appliances';
    airconList: Array<any>;
    aircons: PaginatedResponse<SwitchBotRemoteData>;
    switches: PaginatedResponse<LorawanSwitch>;
    appliances: PaginatedResponse<SwitchBotDeviceData>;
}>();
</script>

<template>
    <Head :title="$t('head.dashboard_device')" />
    <AdminLayout title="head.dashboard_device">
        <div class="h-full overflow-hidden">
            <Label class="fluid-text-sm-lg mx-4 my-3 font-bold">
                {{ room?.name }}
            </Label>
            <ScrollArea class="h-full">
                <DashboardDevicesTab
                    :roomId="room.id"
                    :queryParams="queryParams"
                    :activeTab="activeTab"
                    :aircons="aircons"
                    :switches="switches"
                    :appliances="appliances"
                />

                <DashboardGraphTab :airconList="airconList" />
            </ScrollArea>
        </div>
    </AdminLayout>
</template>
