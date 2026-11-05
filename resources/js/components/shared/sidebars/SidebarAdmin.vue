<script setup lang="ts">
import { Sheet, SheetContent } from '@/components/ui/sheet';
import { SidebarProps } from '@/components/ui/sidebar';
import { SIDEBAR_WIDTH_MOBILE, useSidebar } from '@/components/ui/sidebar/utils';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

withDefaults(defineProps<SidebarProps>(), {
    side: 'left',
    variant: 'sidebar',
    collapsible: 'icon',
});

const { isLaptop, isDesktop, state, openMobile, setOpenMobile, toggleSidebar } = useSidebar();
const { isMobileOrTablet } = useBreakpoints();
</script>

<template>
    <!-- Mobile Sheet -->
    <Sheet v-if="isMobileOrTablet" :open="openMobile" v-bind="$attrs" @update:open="setOpenMobile">
        <SheetContent
            data-sidebar="sidebar"
            data-slot="sidebar"
            data-mobile="true"
            side="left"
            class="bg-sidebar-background w-(--sidebar-width) rounded-r-xl p-0 [&>button]:hidden"
            :style="{ '--sidebar-width': SIDEBAR_WIDTH_MOBILE }"
        >
            <div class="flex h-full w-full flex-col">
                <slot />
            </div>
        </SheetContent>
    </Sheet>

    <!-- Laptop Sidebar -->
    <div v-if="isLaptop" class="relative z-10">
        <!-- Background overlay for toggling -->
        <div v-if="state == 'expanded'" class="bg-background-overlay/70 fixed inset-0 z-0" @click="toggleSidebar" />
        <div
            class="group peer z-10 hidden md:block"
            data-slot="sidebar"
            :data-state="state"
            :data-collapsible="state === 'collapsed' ? collapsible : ''"
            :data-variant="variant"
            :data-side="side"
        >
            <div class="w-[calc(var(--sidebar-width-icon)+(--spacing(4)))]">
                <div
                    :class="
                        cn(
                            'fixed inset-y-0 left-0 z-10 h-svh w-[var(--sidebar-width)] transition-[left,right,width] duration-200 ease-linear md:flex',
                            'py-1 group-data-[collapsible=icon]:w-[calc(var(--sidebar-width-icon)+1rem)]',
                        )
                    "
                    v-bind="$attrs"
                >
                    <div
                        data-sidebar="sidebar"
                        class="group-data-[variant=floating]:border-sidebar-border bg-sidebar-background flex h-full w-full flex-col group-data-[variant=floating]:rounded-r-xl group-data-[variant=floating]:border group-data-[variant=floating]:shadow-sm"
                    >
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div v-if="isDesktop" class="group peer hidden md:block" data-slot="sidebar" :data-state="state" :data-variant="variant" :data-side="side">
        <div class="relative w-(--sidebar-width) bg-transparent transition-[width] duration-200 ease-linear" />
        <div class="fixed inset-y-0 z-20 hidden h-svh w-(--sidebar-width) py-1 duration-200 ease-linear md:flex" v-bind="$attrs">
            <div
                data-sidebar="sidebar"
                class="group-data-[variant=floating]:border-sidebar-border bg-sidebar-background flex h-full w-full flex-col group-data-[variant=floating]:rounded-r-xl group-data-[variant=floating]:border group-data-[variant=floating]:shadow-sm"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
