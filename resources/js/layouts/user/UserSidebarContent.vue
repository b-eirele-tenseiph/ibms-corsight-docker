<script setup lang="ts">
import { SidebarNavItem, SidebarUser } from '@/components/shared/sidebars';
import SidebarUserSettings from '@/components/shared/sidebars/SidebarUserSettings.vue';
import { SidebarContent, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { EmergencyModal } from '@/components/user/emergency';
import { useModal } from '@/composables/useModal';
import { usePermissions } from '@/composables/usePermissions';
import { index as userAmenitiesIndex } from '@/routes/user/amenities';
import { index as userDashboardIndex } from '@/routes/user/dashboard';
import { index as userPromosIndex } from '@/routes/user/promos';
import { index as userRestaurantIndex } from '@/routes/user/restaurant';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Primitive } from 'reka-ui';
import { computed } from 'vue';

// Get the page props once at the top
const page = usePage();
const user = computed(() => (page.props.auth as any).user);

const { can, hasExternalApp } = usePermissions();

const alertConfirmationModal = useModal();

type NavItems = {
    [key: string]: Partial<NavItem>[];
};

const navItems: NavItems = {
    userPages: [
        {
            title: 'navigation.user.dashboard',
            href: userDashboardIndex({ room: user.value.room_number }).url,
            icon: 'lucide:layout-grid',
        },
        {
            permission: 'access amenities',
            title: 'navigation.user.amenities',
            href: userAmenitiesIndex.url(),
            icon: 'ph:barbell',
        },
        {
            permission: 'access promos',
            title: 'navigation.user.promos',
            href: userPromosIndex.url(),
            icon: 'majesticons:megaphone-line',
        },
        {
            permission: 'access restaurant',
            title: 'navigation.user.restaurant',
            href: userRestaurantIndex.url(),
            icon: 'humbleicons:basket',
        },
    ].filter((item) => !item.permission || can(item.permission)),

    externalApps: [
        {
            application: 'ELECTROLUX',
            title: 'navigation.user.washing_machine_1',
            target: 'ibmscontroller://openelectroluxlife',
            icon: 'tpa_washing_machine',
            fallback: 'https://play.google.com/store/apps/details?id=com.electrolux.oneapp.android.electrolux&pcampaignid=web_share',
        },
        {
            application: 'LG',
            title: 'navigation.user.vacuum_cleaner',
            target: 'ibmscontroller://openlgthinq',
            icon: 'tpa_vacuum_cleaner',
            fallback: 'https://play.google.com/store/apps/details?id=com.lgeha.nuts&hl=en&gl=US',
        },
        {
            application: 'AXIS',
            title: 'navigation.user.cctv',
            target: 'ibmscontroller://openaxis',
            icon: 'tpa_cctv',
            fallback: 'https://play.google.com/store/search?q=axis+camera+station&c=apps&hl=en&gl=US',
        },
        {
            application: 'TUYA',
            title: 'navigation.user.curtain',
            target: 'ibmscontroller://opensmartlife',
            icon: 'tpa_window_curtain',
            fallback: 'https://play.google.com/store/apps/details?id=com.tuya.smart&pcampaignid=web_share',
        },
    ].filter((item) => !item.application || hasExternalApp(item.application)),
};
</script>

<template>
    <SidebarUser collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem class="flex justify-center">
                    <SidebarMenuButton size="lg" as-child class="drop-shadow-2xl hover:bg-transparent active:bg-transparent">
                        <Primitive type="button" @click="alertConfirmationModal.open" class="relative size-10">
                            <div class="absolute inset-0 flex items-center justify-center rounded-full bg-red-500">
                                <span class="text-xs font-bold text-white">{{ $t('navigation.user.sos') }}</span>
                            </div>
                        </Primitive>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarNavItem :items="navItems.userPages" />
            <SidebarNavItem :externalApp="true" :items="navItems.externalApps" :label="'navigation.label.apps'" />
            <SidebarUserSettings />
        </SidebarContent>
    </SidebarUser>

    <!-- Mount the dedicated modal component -->
    <EmergencyModal :open="alertConfirmationModal.visible.value" @close="alertConfirmationModal.close" />
</template>
