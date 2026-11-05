import { AppNotification } from '@/types';
import axios from 'axios';
import { ref } from 'vue';

export function useNotifications(listRoute: (page: number) => string) {
    const notifications = ref<AppNotification[]>([]);
    const pageNum = ref(1);
    const hasMore = ref(true);
    const isLoadingMore = ref(false);
    const notifBadge = ref('0');
    const notifCounter = ref(0);

    const normalizeNotification = (n: any): AppNotification => ({
        ...n,
        checked: false,
        data: typeof n.data === 'string' ? JSON.parse(n.data) : (n.data ?? {}),
    });

    const syncBadge = (count: number, badge: string) => {
        notifCounter.value = count;
        notifBadge.value = badge;
    };

    const initialLoad = async () => {
        pageNum.value = 1;
        hasMore.value = true;
        const { data } = await axios.get(listRoute(1));
        notifications.value = (data.notifications?.data ?? []).map(normalizeNotification);
        syncBadge(Number(data.unread_count ?? 0), data.unread_badge ?? '0');
        hasMore.value = Boolean(data.notifications?.next_page_url);
    };

    const loadMore = async () => {
        if (!hasMore.value || isLoadingMore.value) return;
        isLoadingMore.value = true;
        try {
            pageNum.value++;
            const { data } = await axios.get(listRoute(pageNum.value));
            const chunk = (data.notifications?.data ?? []).map(normalizeNotification);
            notifications.value.push(...chunk);
            hasMore.value = Boolean(data.notifications?.next_page_url);
        } finally {
            isLoadingMore.value = false;
        }
    };

    const markSelectedAsRead = async (ids: string[], markReadRoute: string) => {
        if (!ids.length) return;
        const { data } = await axios.post(markReadRoute, { ids });
        const readIds = new Set(ids);
        notifications.value = notifications.value.map((n) => (readIds.has(n.id) ? { ...n, read_at: new Date().toISOString(), checked: false } : n));
        syncBadge(Number(data.unread_count ?? 0), data.unread_badge ?? '0');
    };

    const pushRealtime = (notification: any) => {
        if (notifications.value.some((n) => n.id === notification.id)) return;

        notifications.value.unshift(normalizeNotification(notification));
        if (!notification.read_at) {
            notifCounter.value++;
            notifBadge.value = notifCounter.value > 99 ? '99+' : String(notifCounter.value);
        }
    };

    return {
        notifications,
        hasMore,
        isLoadingMore,
        notifBadge,
        notifCounter,
        initialLoad,
        loadMore,
        markSelectedAsRead,
        pushRealtime,
    };
}
