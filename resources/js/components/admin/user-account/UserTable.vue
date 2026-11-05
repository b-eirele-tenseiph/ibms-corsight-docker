<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import Badge from '@/components/ui/badge/Badge.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { lockAccessUpdate } from '@/routes/admin/management';
import { index } from '@/routes/admin/management/accounts/users';
import { AccountManagementUser } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Props passed from parent, including users list and callback handlers
const props = defineProps<{
    users: Record<string, any>;
    onEdit: (user: AccountManagementUser) => void; // go back here, change to emit
    onDelete: (user: AccountManagementUser) => void;
    queryParams: object;
}>();

// Emit an event to trigger the submit action for the user without opening the modal
defineEmits<{
    (e: 'open-edit', user: AccountManagementUser): void;
}>();

// Responsive breakpoints to control layout visibility
const { isExtraSmallMobile, isSmallMobile, isMobile, isTablet } = useBreakpoints();

// Initialize filters from query params (for sorting/filtering)
const filters = ref({ ...props.queryParams });

// Trigger a page reload with updated filters (used by sortable headers)
function reloadTable() {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}

// Computed data for table: show placeholder row if screen is small or no data exists
const tableData = computed(() => {
    const isSmallScreen = isTablet.value || isMobile.value || isSmallMobile.value;
    return props.users.data.length > 0 || isSmallScreen ? props.users.data : [{}];
});

const statusClassMap = {
    active: 'bg-success-bg text-success-text',
    failed: 'bg-error-bg text-error-text',
    'for creation': 'bg-info-bg text-info-text',
    'for deletion': 'bg-info-bg text-info-text',
} as const;

type StatusKey = keyof typeof statusClassMap;

function badgeClass(status?: string) {
    return (status && statusClassMap[status as StatusKey]) ?? 'bg-background text-primary-text';
}

function triggerSubmit(user: AccountManagementUser) {
    user.processing = true;
    if (user.switch_bot_lock_access?.status === 'failed') {
        router.post(
            lockAccessUpdate({ access: String(user.switch_bot_lock_access.id) }).url,
            {},
            {
                onStart: () => {
                    user.processing = true;
                },
                onFinish: () => {
                    user.processing = false;
                },
            },
        );
    }
}

function statusLabel(status?: string) {
    if (!status) return 'No Access';

    if (status === 'for deletion') return 'for creation';

    return status;
}

const { stopListening: stopLockAccess, listen: listenLockAccess } = useEcho(
    `admin.switch_bot_lock_access`,
    '.switch_bot_keypad.status',
    (data: any) => {
        const reactiveUsers = ref([props.users]);

        const userToUpdate = reactiveUsers.value[0].data.find((u) => u.id === data.device.user_id);
        if (userToUpdate) {
            userToUpdate.switch_bot_lock_access.status = data.device.status;
        }
    },
);

onMounted(() => {
    // Listen for the private channel and the specific event
    listenLockAccess();
});

onUnmounted(() => {
    stopLockAccess();
});
</script>
<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(user, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead class="hidden h-12 py-3 lg:table-cell">
                                {{ $t('table.header.num') }}
                            </TableHead>
                            <SortableTableHead column="room_name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead column="username" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.username') }}
                            </SortableTableHead>
                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.name') }}
                            </SortableTableHead>
                            <!-- <TableHead class="h-12 py-3"> {{ $t('table.header.access_status') }}</TableHead> -->
                            <TableHead class="h-12 py-3">{{ $t('table.header.switch_bot_lock_status') }}</TableHead>
                            <TableHead class="h-12 py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <!-- Table body: render user data rows if any exist -->
                <TableBody class="table-body [&_tr:last-child]:border lg:[&_tr:last-child]:border-0" v-if="props.users.data.length > 0">
                    <template v-for="(user, index) in props.users.data as AccountManagementUser[]" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="hidden h-12 py-3 lg:table-cell">
                                {{ (props.users.current_page - 1) * props.users.per_page + index + 1 }}
                            </TableCell>
                            <TableCell class="h-12 py-3">{{ user.room?.name ?? '-' }}</TableCell>
                            <TableCell class="h-12 py-3">{{ user.username }}</TableCell>
                            <TableCell class="h-12 py-3">{{ user.name }}</TableCell>
                            <TableCell v-if="user.room?.has_switch_bot_keypad_touch" class="h-12 py-3">
                                <Badge
                                    @click="triggerSubmit(user)"
                                    class="fluid-text-xxs-xs h-[24px] rounded-sm px-2 py-1 font-semibold shadow-sm hover:shadow-md"
                                    :class="badgeClass(user.switch_bot_lock_access?.status)"
                                >
                                    <Icon
                                        v-if="user.switch_bot_lock_access?.status === 'failed'"
                                        icon="heroicons-outline:refresh"
                                        class="size-4"
                                        :class="[{ 'animate-spin': user?.processing }]"
                                    />
                                    {{ statusLabel(user.switch_bot_lock_access?.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell v-else class="h-12 py-3"> - </TableCell>

                            <TableCell class="h-12 py-3">
                                <div class="flex h-full justify-start">
                                    <DropdownAction
                                        class="h-full"
                                        :actions="{
                                            update: { label: 'modal.action.update_user' },
                                            delete: { label: 'modal.action.delete_user' },
                                        }"
                                        @update="props.onEdit(user)"
                                        @delete="props.onDelete(user)"
                                    >
                                    </DropdownAction>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <!-- Fallback UI when no users are available -->
                <TableBody v-else>
                    <TableEmpty :colspan="6">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
