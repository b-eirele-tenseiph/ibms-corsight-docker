<script setup lang="ts">
import { SidebarNavCollapsibleItem } from '@/components/shared/sidebars';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { NavItem } from '@/types';
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    externalApp?: boolean;
    items: NavItem[];
    label?: string;
}>();

const page = usePage();

const isNavItemActive = (item: NavItem) => {
    const matchPath = item.routeMatch ?? item.href;
    return page.url.startsWith(matchPath as string);
};
</script>

<template>
    <SidebarGroup v-if="items.length" class="px-2 py-0">
        <SidebarGroupLabel v-if="label" :class="{ 'px-0 text-center group-data-[collapsible=icon]:block': externalApp }">
            {{ $t(label) }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <template v-if="!externalApp">
                    <!-- Regular link -->
                    <SidebarMenuItem v-if="!item.menu" class="flex justify-center">
                        <SidebarMenuButton as-child :is-active="isNavItemActive(item)" :tooltip="$t(item.title)">
                            <Link :href="item.href as string" class="gap-2 py-2">
                                <Icon :icon="item.icon as string" />
                                <span class="text-sm">{{ $t(item.title) }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Dropdown/collapsible -->
                    <SidebarMenuItem v-else>
                        <SidebarNavCollapsibleItem :item="item" :is-active="isNavItemActive(item)" />
                    </SidebarMenuItem>
                </template>

                <!-- External apps (optional) -->
                <template v-else>
                    <SidebarMenuItem class="flex justify-center">
                        <SidebarMenuButton as-child :tooltip="$t(item.title)">
                            <a :href="item.fallback" target="_blank" rel="noopener noreferrer" class="gap-2 py-2">
                                <img class="size-10" :src="`/images/External Apps/${item.icon}.svg`" />
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </template>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
