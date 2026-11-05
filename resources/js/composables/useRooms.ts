import { roomsData } from '@/routes/admin/dashboard';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';

// Shared singleton instance across all callers
let store: any;

export function useRooms() {
    if (store) return store;

    const { t } = useI18n();

    // Current search filter for room list
    const searchTerm = ref<string>('');
    // Per-room data (count, status, active, etc.)
    const roomData = ref<any[]>([]);
    // Loading state for room fetch
    const isLoading = ref<boolean>(false);

    // Number of rooms considered occupied (by count or isOccupied flag from backend)
    const occupiedRoomsCount = computed(
        () =>
            roomData.value.filter((r) => Number(r.count) > 0 || r.isOccupied === true).length,
    );

    // Total number of rooms
    const totalRooms = computed(() => roomData.value.length);

    // Sum of active devices across all rooms
    const totalActiveDevices = computed(() => roomData.value.reduce((sum, r) => sum + r.active, 0));

    // Fetches rooms from the API based on the current search term
    const loadRooms = async () => {
        nextTick(async () => {
            isLoading.value = true;

            try {
                const { data } = await axios.get(roomsData.url(), {
                    params: { search: searchTerm.value || undefined },
                });
                roomData.value = data.rooms;
            } finally {
                isLoading.value = false;
            }
        });
    };

    // update a room by id
    function updateRoom(id: any, patch: any) {
        const i = roomData.value.findIndex((r) => r.id === id);
        if (i === -1) return;
        roomData.value[i] = { ...roomData.value[i], ...patch };
    }

    // get a room by id
    function getRoom(id: any) {
        return roomData.value.find((r) => r.id === id) || null;
    }

    // Listens for room people-count updates
    const { stopListening: stopCount, listen: listenCount } = useEcho('admin.room_people_count', '.people_count_data.new', (data: any) => {
        updateRoom(data.room.id, { count: data.people_count });
    });

    // Listens for occupancy sensor updates
    const { stopListening: stopDevice, listen: listenDevice } = useEcho('admin.lorawan_device', '.lorawan_device_data.new', (data: any) => {
        const occupancy = (data.occu_temp_light ?? []).find((r: any) => r.type == 'occupancy');
        if (!occupancy) return;
        const isOccupied = Boolean(occupancy.context.value);
        updateRoom(occupancy.room_id, {
            isOccupied,
            status: isOccupied ? t('status.occupied') : t('status.vacant'),
        });
    });

    // Listens for room device active/inactive status updates
    const { stopListening: stopRoomActive, listen: listenRoomActive } = useEcho('admin.room.active', '.active_area.new', (data: any) => {
        const { room_id, device_status } = data;
        if (!device_status) return;
        const room = getRoom(room_id);
        if (room) {
            updateRoom(room_id, { active: device_status.active });
        }
    });

    // Starts all room-related WebSocket subscriptions
    function listenRoomEvents() {
        listenCount();
        listenDevice();
        listenRoomActive();
    }

    // Stops all room-related WebSocket subscriptions
    function stopRoomEvents() {
        stopCount();
        stopDevice();
        stopRoomActive();
    }

    // Public API exposed to consumers of this composable
    store = {
        roomData,
        searchTerm,
        isLoading,
        totalActiveDevices,
        totalRooms,
        occupiedRoomsCount,
        loadRooms,
        updateRoom,
        getRoom,
        listenRoomEvents,
        stopRoomEvents,
    };

    return store;
}
