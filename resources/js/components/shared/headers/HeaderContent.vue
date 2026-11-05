<script setup lang="ts">
import { RoleImpersonationPanel } from '@/components/admin/role-impersonation';
import { HeaderTitle, HeaderUserInfo, HeaderUserInfoContent } from '@/components/shared/headers';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { PageTitle } from '@/types';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { NotificationModal } from '../notification';

defineProps<PageTitle>();

const auth = computed(() => usePage().props.auth);
const { isMobileOrTablet, isSmallMobile } = useBreakpoints();
</script>

<template>
    <div class="flex flex-col">
        <RoleImpersonationPanel />

        <header class="border-sidebar-border flex h-15 shrink-0 items-center gap-2 border-b px-4 transition-[width,height] ease-linear">
            <div class="flex items-center gap-2">
                <SidebarTrigger v-if="isMobileOrTablet" icon="lucide:menu" class="text-primary hover:text-primary cursor-pointer" />
                <template v-if="title">
                    <HeaderTitle :page_title="title" />
                </template>
            </div>
            <div class="ml-auto flex items-center gap-2 space-x-2">
                <NotificationModal />
                <!-- User Menu -->
                <div class="flex cursor-pointer flex-col space-y-1">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child v-if="isSmallMobile">
                            <div class="flex flex-row items-center gap-2">
                                <Icon icon="lucide:circle-user" class="text-primary-text xs:size-8 size-7" />
                                <Icon icon="lucide:chevron-down" class="text-primary-text size-3" />
                            </div>
                        </DropdownMenuTrigger>

                        <DropdownMenuTrigger as-child v-else>
                            <HeaderUserInfo :user="auth.user" :show-role="auth.user.guard_name === 'admin'" />
                        </DropdownMenuTrigger>

                        <DropdownMenuContent class="w-56" align="end" :side-offset="10">
                            <HeaderUserInfoContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>
    </div>
</template>
