<script setup lang="ts">
import { HeaderContent } from '@/components/shared/headers';
import { SidebarProvider } from '@/components/shared/sidebars';
import { ToastMessage } from '@/components/shared/toasts';
import { SidebarInset } from '@/components/ui/sidebar';
import { AdminSidebarContent } from '@/layouts/admin';
import type { PageTitle, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<PageTitle>();

const message = computed(() => usePage<SharedData>()?.props.flash);
</script>
<template>
    <SidebarProvider>
        <AdminSidebarContent />
        <SidebarInset class="h-svh">
            <HeaderContent :title="title" />
            <slot />
            <ToastMessage :flash="message" />
        </SidebarInset>
    </SidebarProvider>
</template>
