<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
    label?: string;
}>();

const page = usePage<SharedData>();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="label">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <SidebarMenuItem v-if="!item.menu">
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                        <Link :href="item.href" class="flex items-center gap-2 px-3 py-2">
                            <Icon :icon="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <SidebarMenuItem v-else>
                    <Collapsible class="group/collapsible">
                        <CollapsibleTrigger class="flex w-full items-center justify-between px-3 py-2">
                            <div class="flex items-center gap-2">
                                <Icon :icon="item.icon" />
                                <span class="group-data-[state=open]/collapsible:font-semibold">{{ item.title }}</span>
                            </div>
                            <Icon
                                icon="lucide:chevron-down"
                                class="ml-auto size-3 transition-transform group-data-[state=open]/collapsible:rotate-180"
                            />
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            <div class="pl-6">
                                <SidebarMenuItem v-for="subItem in item.menu" :key="subItem.title">
                                    <SidebarMenuButton as-child :is-active="subItem.href === page.url" :tooltip="subItem.title">
                                        <Link :href="subItem.href" class="flex items-center gap-2 px-3 py-2">
                                            <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
