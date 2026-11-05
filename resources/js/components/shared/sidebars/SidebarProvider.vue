<script setup lang="ts">
import { provideSidebarContext, SIDEBAR_KEYBOARD_SHORTCUT, SIDEBAR_WIDTH, SIDEBAR_WIDTH_ICON } from '@/components/ui/sidebar/utils';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { cn } from '@/lib/utils';
import { useEventListener } from '@vueuse/core';
import { TooltipProvider } from 'reka-ui';
import { computed, type HTMLAttributes, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        isUser?: boolean;
    }>(),
    {
        isUser: false,
    },
);

const { isDesktop, isLaptop, isMobile, isTablet, isMobileOrTablet } = useBreakpoints();

const openMobile = ref(false);
const openLaptop = ref(false);

function setOpenMobile(value: boolean) {
    openMobile.value = value;
}

// Helper to toggle the sidebar.
function toggleSidebar() {
    if (isMobileOrTablet.value) {
        setOpenMobile(!openMobile.value);
    } else if (isLaptop.value) {
        openLaptop.value = !openLaptop.value;
    }
}

useEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key === SIDEBAR_KEYBOARD_SHORTCUT && (event.metaKey || event.ctrlKey)) {
        event.preventDefault();
        toggleSidebar();
    }
});

const state = computed(() => {
    if (props.isUser) {
        return 'collapsed';
    } else {
        if (isLaptop.value) return openLaptop.value ? 'expanded' : 'collapsed';
    }
    return 'expanded';
});

provideSidebarContext({
    state,
    isMobile,
    isTablet,
    isLaptop,
    isDesktop,
    openMobile,
    setOpenMobile,
    toggleSidebar,
});
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <div
            data-slot="sidebar-wrapper"
            :style="{
                '--sidebar-width': SIDEBAR_WIDTH,
                '--sidebar-width-icon': SIDEBAR_WIDTH_ICON,
            }"
            :class="cn('group/sidebar-wrapper flex min-h-svh w-full has-data-[variant=inset]:bg-white', props.class)"
            v-bind="$attrs"
        >
            <slot />
        </div>
    </TooltipProvider>
</template>
