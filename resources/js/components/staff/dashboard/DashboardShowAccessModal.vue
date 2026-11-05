<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { lockAccessUpdate } from '@/routes/staff';
import { AccountManagementUser, Room, RoomAccessWithUser } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import dayjs from 'dayjs';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Props & emits
const props = defineProps<{
    showAccessModal: {
        show: boolean;
        room: Room;
        onCancel: () => void;
        onSubmit: () => void;
    };
}>();

const emit = defineEmits(['edit', 'delete', 'add']);
const fmtDate = (v: string | Date) => dayjs(v).format('MMMM D');

// Action handlers
const editAccess = (access: any) => {
    const userWithAccess = {
        ...access.user,
        room_access: access,
    };
    emit('edit', userWithAccess);
};

const deleteAccess = (access: RoomAccessWithUser) => {
    emit('delete', access);
};
const addAccess = () => {
    emit('add', props.showAccessModal.room);
};

const showAllAccesses = ref(false);

const hasAccesses = computed(() => {
    return (props.showAccessModal.room.room_accesses?.length || 0) > 0;
});

const limitedAccesses = computed(() => {
    const all = props.showAccessModal.room.room_accesses || [];
    return showAllAccesses.value ? all : all.slice(0, 2);
});

const hasMoreAccesses = computed(() => {
    return (props.showAccessModal.room.room_accesses?.length || 0) > 2;
});

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

function statusLabel(status?: string) {
    if (!status) return 'No Access';

    if (status === 'for deletion') return 'for creation';
    return status;
}

const { stopListening: stopLockAccess, listen: listenLockAccess } = useEcho(
    `admin.switch_bot_lock_access`,
    '.switch_bot_keypad.status',
    (data: any) => {
        const updateStatus = (room_accesses: any) => {
            // Ensure that `room_accesses` is properly iterated
            const access = room_accesses?.find((a: any) => a.user_id === data.device.user_id);

            // If the access exists, update the status
            if (access?.switch_bot_lock_access) {
                access.switch_bot_lock_access.status = data.device.status; // Assuming the `status` comes from `data`
            }
        };

        // Update the specific room (since props only contains one room)
        updateStatus(props.showAccessModal.room.room_accesses);
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
    <!-- ShowAccessModal.vue -->
    <ModalForm
        :open="showAccessModal.show"
        title="form.label.room_access"
        icon="fluent:key-multiple-24-regular"
        confirmLabel="action.apply"
        @close="showAccessModal.onCancel"
        @submit="showAccessModal.onSubmit"
    >
        <template #default>
            <div class="flex flex-col space-y-3">
                <!-- Header Row: Room name and View All -->
                <div class="flex items-center justify-between">
                    <div class="fluid-text-xs-sm font-bold">
                        {{ showAccessModal.room.name }}
                    </div>
                    <!-- Toggle Button -->
                    <button
                        v-if="hasMoreAccesses"
                        class="text-primary-link-text cursor-pointer text-xs font-semibold underline"
                        @click="showAllAccesses = !showAllAccesses"
                    >
                        {{ showAllAccesses ? 'View Less' : 'View All' }}
                    </button>
                </div>

                <!-- List or empty state -->
                <template v-if="hasAccesses">
                    <template v-for="access in limitedAccesses" :key="access.id">
                        <div
                            class="border-border background bg-table-header-background flex flex-row items-center justify-between rounded-md border p-3"
                        >
                            <!-- Access Info -->
                            <div class="flex w-full flex-col space-y-2">
                                <div class="fluid-text-xs-sm text-primary-text truncate font-medium">
                                    {{ access.user.name }}
                                </div>
                                <span v-if="!access.is_permanent" class="fluid-text-xs-sm text-background text-xs font-medium">
                                    <span class="bg-primary rounded px-1 py-0.5">
                                        {{ fmtDate(access.start_date) }} - {{ fmtDate(access.end_date) }}
                                    </span>
                                </span>
                                <span v-else class="fluid-text-xs-sm text-background text-xs font-medium">
                                    <span class="bg-primary rounded px-1 py-0.5">
                                        {{ $t('form.label.permanent') }}
                                    </span>
                                </span>
                            </div>
                            <!-- Actions -->
                            <div class="ml-3 flex items-center gap-2">
                                <Badge
                                    v-if="props.showAccessModal.room.has_switch_bot_keypad_touch"
                                    @click="triggerSubmit(access)"
                                    class="fluid-text-xxs-xs h-[24px] w-auto rounded-sm px-2 py-1 font-semibold shadow-sm hover:shadow-md"
                                    :class="badgeClass(access.switch_bot_lock_access?.status)"
                                >
                                    <Icon
                                        v-if="access.switch_bot_lock_access?.status === 'failed'"
                                        icon="heroicons-outline:refresh"
                                        class="size-4"
                                        :class="[{ 'animate-spin': access?.processing }]"
                                    />
                                    {{ statusLabel(access.switch_bot_lock_access?.status) }}
                                </Badge>
                                <Icon icon="bx:edit" class="size-4 cursor-pointer sm:size-5" @click="editAccess(access)" />
                                <Icon
                                    icon="material-symbols:delete-outline-sharp"
                                    class="size-4 cursor-pointer sm:size-5"
                                    @click="deleteAccess(access)"
                                />
                            </div>
                        </div>
                    </template>
                </template>
                <div v-else class="text-destructive fluid-text-xs-sm">{{ $t('form.label.no_access') }}</div>
            </div>

            <!-- Add New Access -->
            <div class="text-primary-link-text mt-5 flex w-fit cursor-pointer items-center gap-2 hover:underline" @click="addAccess">
                <Icon icon="simple-line-icons:plus" class="bg-table-header-background size-4 sm:size-5" />
                <span class="fluid-text-xs-sm font-medium">
                    {{ $t('form.label.add_new_access') }}
                </span>
            </div>
        </template>
    </ModalForm>
</template>
