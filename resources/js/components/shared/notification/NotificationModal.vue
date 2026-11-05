<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { useNotifications } from '@/composables/useNotifications';
import { useScrollDetection } from '@/composables/useScrollDetection';
import { check, download as downloadFile } from '@/routes/admin/management/settings/file';
import { index, markAsRead, readOne } from '@/routes/notifications';
import { AppNotification } from '@/types';
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { useEchoNotification } from '@laravel/echo-vue';
import { computed, inject, onMounted, ref } from 'vue';
import { NotificationItem, NotificationToolbar } from '.';

const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

const page = usePage();
const authUser = computed(() => (page.props as any).auth.user);
const isAdmin = computed(() => authUser.value?.guard_name === 'admin');

const cfg = computed(() => ({
    list: (p: number) => index({ query: { page: p } }).url,
    markRead: () => markAsRead.url(),
    readOne: (id: string) => readOne(id).url,
    channel: isAdmin.value ? `admin.notification.${authUser.value.id}` : `user.${authUser.value.id}`,
}));

const {
    notifications,
    hasMore,
    isLoadingMore,
    notifBadge,
    notifCounter,
    initialLoad,
    loadMore,
    markSelectedAsRead: markRead,
    pushRealtime,
} = useNotifications(cfg.value.list);

const { scrollContainer } = useScrollDetection(loadMore, hasMore);

// Track whether we've loaded notifications on the first popover open
const hasLoaded = ref(false);

const handlePopoverOpen = (open: boolean) => {
    if (open && !hasLoaded.value) {
        hasLoaded.value = true;
        initialLoad();
    }
};

// Initialize badge from Inertia shared props
const sharedUnread = (page.props as any).unread_notifications as string;
notifBadge.value = String(sharedUnread ?? '0');
notifCounter.value = parseInt(String(sharedUnread ?? '0')) || 0;

const isMainPopoverOpen = ref(false);
const isBulkSelecting = ref(false);
const openSubPopoverId = ref<string | null>(null);

const selectedNotifications = computed(() => notifications.value.filter((n) => n.checked).map((n) => n.id));
const checkable = computed(() => notifications.value.filter((n) => !n.read_at));
const checkAllValue = computed(() => checkable.value.length > 0 && checkable.value.every((n) => n.checked));
const isIndeterminate = computed(() => {
    const checked = checkable.value.filter((n) => n.checked);
    return checked.length > 0 && checked.length < checkable.value.length;
});

const anySelected = computed(() => checkAllValue.value || isIndeterminate.value);

const toggleCheckAll = (checked: boolean) => {
    notifications.value.forEach((n) => {
        if (!n.read_at) n.checked = checked;
    });
};

const markSelectedAsRead = async () => {
    await markRead(selectedNotifications.value, cfg.value.markRead());
    isBulkSelecting.value = false;
};

const closeBulkSelect = () => {
    isBulkSelecting.value = false;
    openSubPopoverId.value = null;
    notifications.value = notifications.value.map((n) => ({ ...n, checked: false }));
};

const viewNotification = (notification: AppNotification) => {
    const { id, data } = notification;
    const { url, metadata } = data;

    if (metadata?.download_url) {
        return;
    }

    if (url) {
        router.put(cfg.value.readOne(id), { url }, { preserveState: false });
        return;
    }

    markRead([id], cfg.value.markRead());
};

onMounted(() => {
    useEchoNotification(cfg.value.channel, pushRealtime).listen();
});

const downloadGenerateReport = (filePath: string) => {
    router.post(
        check.url(),
        { file: filePath },
        {
            onSuccess: () => {
                window.location.href = downloadFile({ query: { file: filePath } }).url;
            },
            onError: (data) => {
                toast.error(data.message, {
                    classes: {
                        toast: 'border-l-3 border-l-error',
                        icon: 'text-error [&>svg]:size-4 xs:[&>svg]:size-5',
                    },
                });
            },
        },
    );
};
</script>

<template>
    <Popover v-model:open="isMainPopoverOpen" @update:open="handlePopoverOpen">
        <PopoverTrigger class="relative cursor-pointer">
            <Icon icon="lucide:bell" class="text-primary-text size-7.5" />
            <div
                v-if="notifCounter > 0"
                class="bg-destructive absolute top-0 inline-flex size-4.5 items-center justify-center rounded-full text-[8px] font-semibold text-white"
            >
                {{ notifBadge }}
            </div>
        </PopoverTrigger>

        <PopoverContent
            :side-offset="12"
            class="bg-background mr-4 flex max-h-128 min-h-0 w-md flex-col gap-0 overflow-hidden rounded-md p-0 pt-3 pl-3 shadow-lg max-[500px]:w-[calc(100vw-2rem)]"
        >
            <!-- Header -->
            <div class="flex shrink-0 items-center justify-between border-b pb-2">
                <h3 class="text-base-xl font-semibold">{{ $t('navigation.notification') }}</h3>
            </div>

            <template v-if="notifications.length">
                <!-- Bulk actions -->
                <NotificationToolbar
                    v-if="isBulkSelecting"
                    class="mt-2 mr-3"
                    :check-all-value="checkAllValue"
                    :is-indeterminate="isIndeterminate"
                    :any-selected="anySelected"
                    @toggle-all="toggleCheckAll"
                    @mark-as-read="markSelectedAsRead"
                    @cancel="closeBulkSelect"
                />

                <!-- Items -->
                <div ref="scrollContainer" class="min-h-0 flex-1 overflow-y-auto pt-2 pr-3 pb-3">
                    <div class="space-y-1">
                        <NotificationItem
                            v-for="notif in notifications"
                            :key="notif.id"
                            :notif="notif"
                            :is-bulk-selecting="isBulkSelecting"
                            :is-popover-open="openSubPopoverId === notif.id"
                            @update:popover-open="(open) => (openSubPopoverId = open ? notif.id : null)"
                            @select-for-bulk="
                                () => {
                                    isBulkSelecting = true;
                                    notif.checked = true;
                                }
                            "
                            @download-click="downloadGenerateReport"
                            @click="viewNotification(notif)"
                        />

                        <div v-if="isLoadingMore" class="flex items-center justify-center py-4">
                            <Icon icon="line-md:loading-twotone-loop" class="text-primary size-8" />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty state -->
            <template v-else>
                <div class="xs:min-h-100 flex h-full flex-col items-center justify-center">
                    <img src="/images/Illustrations/Notify.svg" alt="No notifications" class="size-50" loading="lazy" />
                    <p class="fluid-text-xs-sm mt-6 text-center font-medium">{{ $t('page.notification.no_notifications') }}</p>
                    <p class="fluid-text-xs-sm mt-4 text-center">{{ $t('page.notification.we_will_notify_you') }}</p>
                </div>
            </template>
        </PopoverContent>
    </Popover>
</template>
