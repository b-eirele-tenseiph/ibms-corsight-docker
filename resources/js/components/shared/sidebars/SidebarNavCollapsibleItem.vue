<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem, useSidebar } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    item: NavItem;
    isActive?: boolean;
}>();

const { state } = useSidebar();
const page = usePage();

// open the collapsible if any of the subitems match the current route
const showCollapsedItem = ref(props.item.menu?.some((sub) => page.url.startsWith(sub.href)) ?? false);

// highlight parent item if in collapsed mode and any subitem is active
const shouldHighlightParent = computed(() => state?.value === 'collapsed' && props.isActive);

const toggleCollapsedItem = () => {
    showCollapsedItem.value = !showCollapsedItem.value;
};
</script>

<template>
    <Collapsible :open="showCollapsedItem" @update:open="toggleCollapsedItem" class="group/collapsible">
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <CollapsibleTrigger class="flex w-full" as-child>
                        <SidebarMenuButton class="flex gap-2 py-2" :is-active="shouldHighlightParent" :tooltip="$t(item.title)">
                            <Icon :icon="item.icon" />
                            <span class="text-sm group-data-[state=open]/collapsible:font-semibold">
                                {{ $t(item.title) }}
                            </span>
                            <Icon icon="lucide:chevron-right" class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-90" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>

                    <!-- Expanded mode subitems -->
                    <CollapsibleContent class="w-full">
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.menu || []" :key="$t(subItem.title)">
                                <SidebarMenuSubButton as-child :is-active="page.url.startsWith(subItem.href)" :tooltip="$t(subItem.title)">
                                    <Link :href="subItem.href" class="flex items-center gap-2 px-3 py-2">
                                        {{ $t(subItem.title) }}
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>

                    <!-- Collapsed dropdown -->
                    <DropdownMenuContent v-if="state === 'collapsed'" side="right" align="start" class="ml-2 border-0">
                        <DropdownMenuItem v-for="subItem in item.menu || []" :key="$t(subItem.title)" as-child>
                            <Link
                                :href="subItem.href"
                                class="flex w-full items-center gap-2 px-3 py-2 text-xs"
                                :class="{ 'font-semibold': page.url.startsWith(subItem.href) }"
                            >
                                <span>{{ $t(subItem.title) }}</span>
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenuTrigger>
            </DropdownMenu>
        </SidebarMenuItem>
    </Collapsible>
</template>
