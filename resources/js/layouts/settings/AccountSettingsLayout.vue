<script setup lang="ts">
import { AccountInfoHeader } from '@/components/admin/account-info';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { passwordEdit, profileEdit } from '@/routes/admin/settings';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { AdminPageLayout } from '../admin';

const page = usePage();

const sidebarNavItems: NavItem[] = [
    {
        title: 'navigation.admin.profile',
        href: profileEdit.url(),
    },
    {
        title: 'navigation.admin.password',
        href: passwordEdit.url(),
    },
];
</script>

<template>
    <AdminPageLayout>
        <div class="px-4 py-6">
            <AccountInfoHeader title="page.account_information.header.account" description="page.account_information.header.account_subtitle" />
            <div class="flex flex-col space-y-2 lg:flex-row lg:space-x-12">
                <aside class="w-full max-w-xl lg:w-48">
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
                <Separator class="my-6 lg:hidden" />
                <div class="flex-1 px-2 md:max-w-2xl">
                    <section class="max-w-xl space-y-12">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </AdminPageLayout>
</template>
