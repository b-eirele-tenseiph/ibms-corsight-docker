<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import { AdminPageLayout } from '@/layouts/admin';
import { index as roleIndex } from '@/routes/admin/management/settings/roles';
import { index as sceneIndex } from '@/routes/admin/management/settings/scenes';
import { generalIndex } from '@/routes/admin/management/settings/system';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const { can } = usePermissions();

const sidebarNavItems: NavItem[] = [
    {
        title: 'navigation.admin.system',
        href: generalIndex.url(),
        routeMatch: '/admin/management/settings/system',
    },
    ...[
        {
            permission: 'manage roles',
            title: 'navigation.admin.role',
            href: roleIndex.url(),
        },
        {
            permission: 'manage scenes',
            title: 'navigation.admin.scene',
            href: sceneIndex.url(),
        },
    ].filter((item) => can(item.permission)),
];

const page = usePage();

const isActive = (item: NavItem) => {
    const match = (item.routeMatch ?? item.href) as string;
    return page.url.startsWith(match);
};
</script>

<template>
    <AdminPageLayout>
        <div class="mb-2 flex items-center gap-2">
            <Button
                v-for="item in sidebarNavItems"
                :key="item.href"
                variant="ghost"
                as-child
                class="relative px-2"
                :class="
                    isActive(item) &&
                    'after:bg-primary hover:text-primary hover:bg-primary/10 after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-full after:rounded-sm'
                "
            >
                <Link
                    :href="item.href"
                    class="inline-flex flex-col items-center justify-center px-1 pb-1 text-sm font-semibold"
                    :class="isActive(item) ? 'text-primary font-bold' : 'text-primary-text'"
                >
                    {{ $t(item.title) }}
                </Link>
            </Button>
        </div>
        <slot />
    </AdminPageLayout>
</template>
