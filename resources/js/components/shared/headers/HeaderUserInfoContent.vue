<script setup lang="ts">
import { HeaderUserInfo } from '@/components/shared/headers';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { logout as adminLogout } from '@/routes/admin';
import { index, store } from '@/routes/admin/management/impersonate';
import { profileEdit } from '@/routes/admin/settings';
import { logout as userLogout } from '@/routes/index';
import type { SharedData, User } from '@/types';
import { Icon } from '@iconify/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const handleLogout = () => {
    router.flushAll();
};

const props = defineProps<{
    user: User;
}>();

const isAdmin = computed(() => props.user.guard_name === 'admin');
const isSuperAdmin = computed(() => props.user.role === 'super admin');
const impersonation = usePage<SharedData>().props.impersonation;
const existingRoles = ref([]);

const switchRole = (role: string) => {
    router.post(store.url(), { role });
};

onMounted(() => {
    axios.get(index.url()).then(({ data }) => (existingRoles.value = data));
});
</script>

<template>
    <DropdownMenuLabel class="xs:hidden flex p-0 font-normal">
        <div class="items-center gap-2 px-1 py-1.5 text-left">
            <HeaderUserInfo :user="user" :show-role="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuGroup>
        <DropdownMenuGroup v-if="isSuperAdmin">
            <DropdownMenuLabel class="fluid-text-xs-sm font-normal">
                <div class="flex items-center gap-4">
                    <Icon icon="lucide:eye" class="text-secondary-text size-4 shrink-0" />
                    <span>{{ $t('navigation.admin.view_as_role') }}</span>
                </div>
            </DropdownMenuLabel>

            <div class="max-h-36 space-y-px overflow-y-auto pr-1">
                <DropdownMenuItem
                    v-for="role in existingRoles"
                    :key="role"
                    @click="switchRole(role)"
                    :class="{ 'bg-accent': impersonation?.role === role }"
                >
                    {{ role }}
                </DropdownMenuItem>
            </div>
        </DropdownMenuGroup>

        <DropdownMenuSeparator v-if="isSuperAdmin" class="mx-2" />

        <DropdownMenuItem :as-child="true" class="cursor-pointer" v-if="isAdmin">
            <Link class="fluid-text-xs-sm block w-full py-2" :href="profileEdit.url()" prefetch as="button">
                <Icon icon="lucide:edit" class="mr-2 size-4" />
                <span>{{ $t('navigation.admin.account_info') }}</span>
            </Link>
        </DropdownMenuItem>

        <DropdownMenuSeparator v-if="isAdmin" class="mx-2" />

        <DropdownMenuItem :as-child="true" class="cursor-pointer">
            <Link
                class="fluid-text-xs-sm block w-full py-2"
                method="post"
                :href="isAdmin ? adminLogout.url() : userLogout.url()"
                @click="handleLogout"
                as="button"
            >
                <Icon icon="lucide:log-out" class="mr-2 size-4" />
                <span>{{ $t('layout.logout') }}</span>
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
