<script setup lang="ts">
import { SidebarAdmin, SidebarLogo, SidebarNavItem } from '@/components/shared/sidebars';
import {
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
    useSidebar,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { index as climateAnalyticsIndex } from '@/routes/admin/climate-analytics';
import { index as electricMonitoringIndex } from '@/routes/admin/electric-monitoring';
import { index as adminsIndex } from '@/routes/admin/management/accounts/admins';
import { index as usersIndex } from '@/routes/admin/management/accounts/users';
import { index as logsIndex } from '@/routes/admin/management/logs';
import { index as lorawanDevicesIndex } from '@/routes/admin/management/lorawan/devices';
import { index as lorawanGatewaysIndex } from '@/routes/admin/management/lorawan/gateways';
import { index as roomsIndex } from '@/routes/admin/management/rooms';
import { generalIndex as settingsIndex } from '@/routes/admin/management/settings/system';
import { index as switchbotAccountsIndex } from '@/routes/admin/management/switchbot/accounts';
import { index as switchbotDevicesIndex } from '@/routes/admin/management/switchbot/devices';
import { index as switchbotHubsIndex } from '@/routes/admin/management/switchbot/hubs';
import { index as switchbotRemotesIndex } from '@/routes/admin/management/switchbot/remotes';
import { index as corsightAccountsIndex } from '@/routes/admin/management/corsight/accounts';
import { index as corsightDevicesIndex } from '@/routes/admin/management/corsight/devices';
import { index as corsightPeopleIndex } from '@/routes/admin/management/corsight/people';
import { index as airConsIndex } from '@/routes/admin/monitoring-controls/aircons';
import { index as plugsIndex } from '@/routes/admin/monitoring-controls/plugs';
import { index as switchesIndex } from '@/routes/admin/monitoring-controls/switches';
import { index as roomMonitoringIndex } from '@/routes/admin/room-monitoring';
import { index as waterBillingIndex } from '@/routes/admin/water-billing-report';
import { index as foodsIndex } from '@/routes/crew/foods';
import { history as ordersHistoryIndex } from '@/routes/crew/orders';
import { index as amenitiesIndex } from '@/routes/staff/amenity_management';
import { index as promosIndex } from '@/routes/staff/promo_management';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type NavItems = {
    [key: string]: NavItem[];
};

const { state } = useSidebar();

const { can, homeLink } = usePermissions();

const navItems = computed<NavItems>(() => {
    return {
        overview: [
            {
                title: 'navigation.admin.dashboard',
                href: homeLink.value,
                icon: 'lucide:layout-grid',
            },
            {
                permission: 'climate analytics',
                title: 'navigation.admin.climate_analytics',
                href: climateAnalyticsIndex.url(),
                icon: 'lucide:snowflake',
            },
            {
                permission: 'room monitoring',
                title: 'navigation.admin.room_monitoring',
                href: roomMonitoringIndex.url(),
                icon: 'lucide-lab:floor-plan',
            },
            {
                permission: 'electric monitoring',
                title: 'navigation.admin.electric_monitoring',
                href: electricMonitoringIndex.url(),
                icon: 'lucide:chart-spline',
            },
            {
                permission: 'manage water billing',
                title: 'navigation.admin.water_billing',
                href: waterBillingIndex.url(),
                icon: 'lucide:droplets',
            },
        ].filter((item) => !item.permission || can(item.permission)),

        controls: [
            {
                permission: 'monitoring controls',
                title: 'navigation.admin.climate',
                href: airConsIndex.url(),
                icon: 'streamline:hotel-air-conditioner',
            },
            {
                permission: 'monitoring controls',
                title: 'navigation.admin.switches',
                href: switchesIndex.url(),
                icon: 'lucide:lamp-ceiling',
            },
            {
                permission: 'monitoring controls',
                title: 'navigation.admin.plugs',
                href: plugsIndex.url(),
                icon: 'material-symbols:outlet-outline',
            },
        ].filter((item) => !item.permission || can(item.permission)),

        devices: [
            {
                permission: 'manage lorawan',
                title: 'navigation.admin.lorawan',
                href: lorawanGatewaysIndex.url(),
                icon: 'iconoir:antenna',
                menu: [
                    {
                        title: 'navigation.admin.lorawan_gateway',
                        href: lorawanGatewaysIndex.url(),
                    },
                    {
                        title: 'navigation.admin.lorawan_device',
                        href: lorawanDevicesIndex.url(),
                    },
                ],
            },
            {
                permission: 'manage switchbot',
                title: 'navigation.admin.switch_bot',
                href: switchbotAccountsIndex.url(),
                icon: 'fluent:iot-16-regular',
                menu: [
                    {
                        title: 'navigation.admin.switch_bot_account',
                        href: switchbotAccountsIndex.url(),
                    },
                    {
                        title: 'navigation.admin.switch_bot_hub',
                        href: switchbotHubsIndex.url(),
                    },
                    {
                        title: 'navigation.admin.switch_bot_device',
                        href: switchbotDevicesIndex.url(),
                    },
                    {
                        title: 'navigation.admin.switch_bot_remote',
                        href: switchbotRemotesIndex.url(),
                    },
                ],
            },
            {
                permission: 'manage corsight',
                title: 'navigation.admin.corsight',
                href: corsightAccountsIndex.url(),
                icon: 'lucide:scan-face',
                menu: [
                    {
                        title: 'navigation.admin.corsight_account',
                        href: corsightAccountsIndex.url(),
                    },
                    {
                        title: 'navigation.admin.corsight_device',
                        href: corsightDevicesIndex.url(),
                    },
                    {
                        title: 'navigation.admin.corsight_person',
                        href: corsightPeopleIndex.url(),
                    },
                ],
            },
        ].filter((item) => !item.permission || can(item.permission)),

        hospitality: [
            {
                permission: 'manage amenities',
                title: 'navigation.staff.amenities',
                href: amenitiesIndex.url(),
                icon: 'iconoir:gym',
            },
            {
                permission: 'manage promos',
                title: 'navigation.staff.promos',
                href: promosIndex.url(),
                icon: 'grommet-icons:announce',
            },
            {
                permission: 'manage orders',
                title: 'navigation.crew.food_menu',
                href: foodsIndex.url(),
                icon: 'fluent:food-20-regular',
            },
            {
                permission: 'manage orders',
                title: 'navigation.crew.order_history',
                href: ordersHistoryIndex.url(),
                icon: 'mdi:basket-fill',
            },
        ].filter((item) => !item.permission || can(item.permission)),

        administration: [
            {
                permission: 'manage rooms',
                title: 'navigation.admin.rooms',
                href: roomsIndex.url(),
                icon: 'lucide:door-open',
            },
            {
                permission: 'manage logs',
                title: 'navigation.admin.logs',
                href: logsIndex.url(),
                icon: 'mdi:clipboard-text-history-outline',
            },
            {
                permission: 'manage admin accounts|manage user accounts',
                title: 'navigation.admin.account_management',
                href: adminsIndex.url(),
                icon: 'lucide:users',
                menu: [
                    {
                        permission: 'manage admin accounts',
                        title: 'navigation.admin.admins',
                        href: adminsIndex.url(),
                    },
                    {
                        permission: 'manage user accounts',
                        title: 'navigation.admin.users',
                        href: usersIndex.url(),
                    },
                ].filter((item) => !item.permission || can(item.permission)),
            },
            {
                title: 'navigation.admin.settings',
                href: settingsIndex.url(),
                icon: 'lucide:settings',
                routeMatch: '/admin/management/settings',
            },
        ].filter((item) => !item.permission || can(item.permission)),
    };
});
</script>

<template>
    <SidebarAdmin collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="drop-shadow-2xl hover:bg-transparent active:bg-transparent">
                        <SidebarTrigger v-if="state == 'collapsed'" class="text-primary cursor-pointer" icon="lucide:chevron-right" />

                        <Link v-else :href="homeLink" as="div" class="flex justify-center">
                            <SidebarLogo class="text-center" />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarNavItem :items="navItems.overview" label="navigation.label.overview" />
            <SidebarNavItem :items="navItems.controls" label="navigation.label.control" />
            <SidebarNavItem :items="navItems.devices" label="navigation.label.devices" />
            <SidebarNavItem :items="navItems.hospitality" label="navigation.label.hospitality" />
            <SidebarNavItem :items="navItems.administration" label="navigation.label.management" />
        </SidebarContent>

        <SidebarFooter class="flex items-center justify-center" v-if="state == 'expanded'">
            <label class="text-secondary-text text-xs font-semibold">
                {{ $t('layout.footer.company_name') }}
            </label>
        </SidebarFooter>
    </SidebarAdmin>
</template>
