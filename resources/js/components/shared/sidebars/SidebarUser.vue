<script setup lang="ts">
import { Sheet, SheetContent } from '@/components/ui/sheet';
import { SidebarProps } from '@/components/ui/sidebar';
import { useSidebar } from '@/components/ui/sidebar/utils';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

withDefaults(defineProps<SidebarProps>(), {
    side: 'left',
    variant: 'sidebar',
    collapsible: 'icon',
});

const { state, openMobile, setOpenMobile } = useSidebar();
const { isMobileOrTablet } = useBreakpoints();
</script>

<template>
    <component
        :is="isMobileOrTablet ? Sheet : 'div'"
        v-bind="isMobileOrTablet ? { open: openMobile, ...$attrs, 'onUpdate:open': setOpenMobile } : {}"
        class="relative z-10"
    >
        <component
            :is="isMobileOrTablet ? SheetContent : 'div'"
            v-bind="
                isMobileOrTablet
                    ? {
                          side: 'left',
                          'data-sidebar': 'sidebar',
                          'data-slot': 'sidebar',
                          'data-mobile': 'true',
                          class: 'border-none bg-transparent w-(--sidebar-width) [&>button]:hidden', //check this
                          style: { '--sidebar-width': '3.3rem' },
                      }
                    : {}
            "
        >
            <div class="group peer z-10" data-slot="sidebar" :data-state="state" data-collapsible="icon" :data-variant="variant" :data-side="side">
                <div class="w-[calc(var(--sidebar-width-icon)+(--spacing(5)))]">
                    <div
                        :class="
                            cn(
                                'fixed inset-y-0 left-0 z-10 h-svh w-[var(--sidebar-width)] transition-[left,right,width] duration-200 ease-linear md:flex',
                                'py-1 group-data-[collapsible=icon]:w-[calc(var(--sidebar-width-icon)+1.5rem)]',
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
        </component>
    </component>
</template>
