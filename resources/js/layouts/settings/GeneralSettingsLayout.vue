<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { usePermissions } from '@/composables/usePermissions';
import { fileIndex, generalIndex, rateIndex } from '@/routes/admin/management/settings/system';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';

const page = usePage();

const { can } = usePermissions();

const sidebarNavItems: NavItem[] = [
    {
        title: 'navigation.admin.general',
        href: generalIndex.url(),
    },
    {
        permission: 'manage rates',
        title: 'navigation.admin.rate',
        href: rateIndex.url(),
    },
    {
        permission: 'manage files',
        title: 'navigation.admin.files',
        href: fileIndex.url(),
    },
].filter((item) => !item.permission || can(item.permission));

const isDesktop = useMediaQuery('(min-width: 1024px)');
</script>

<template>
    <div class="flex h-full min-h-0 flex-col space-y-2 lg:flex-row lg:space-x-6">
        <aside class="w-full lg:w-48">
            <nav class="flex flex-col space-y-1 space-x-0">
                <Button
                    v-for="item in sidebarNavItems"
                    :key="item.href"
                    variant="ghost"
                    :class="['w-full justify-start text-sm', { 'bg-accent': page.url.startsWith(item.href as string) }]"
                    as-child
                >
                    <Link :href="item.href">
                        {{ $t(item.title) }}
                    </Link>
                </Button>
            </nav>
        </aside>

        <Separator :orientation="isDesktop ? 'vertical' : 'horizontal'" class="border-border mt-2 mb-6 lg:my-0" />

        <div class="flex min-h-0 w-full flex-1 flex-col">
            <div class="flex h-full flex-col space-y-4">
                <slot />
            </div>
        </div>
    </div>
</template>
