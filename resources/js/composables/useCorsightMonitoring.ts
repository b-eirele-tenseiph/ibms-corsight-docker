import { corsightData } from '@/routes/admin/dashboard';
import {
    CctvTimeRange,
    CorsightDemographics,
    CorsightDeviceOption,
    CorsightDwellTime,
    CorsightPeopleCount,
    CorsightRecentLog,
    CorsightRoom,
} from '@/types';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, ref, watch } from 'vue';

// Set to true to fall back to the bundled dummy data while the backend is unavailable.
const USE_DUMMY = false;

const AGE_RANGES = ['20-29', '30-39', '40-49', '50+'] as const;

const DUMMY_ROOMS: CorsightRoom[] = [
    { name: 'Main Lobby', active_cameras: 2, total_cameras: 2, occupancy: 6 },
    { name: 'Auditorium A', active_cameras: 1, total_cameras: 1, occupancy: 4 },
    { name: 'Conference Room 2', active_cameras: 1, total_cameras: 2, occupancy: 6 },
];

const DUMMY_DEVICES: CorsightDeviceOption[] = [
    { id: 1, name: 'Lobby Cam 1', room: 'Main Lobby' },
    { id: 2, name: 'Lobby Cam 2', room: 'Main Lobby' },
    { id: 3, name: 'Auditorium Cam 1', room: 'Auditorium A' },
    { id: 4, name: 'Conf Room Cam 1', room: 'Conference Room 2' },
    { id: 5, name: 'Conf Room Cam 2', room: 'Conference Room 2' },
];

const buildDummyDwell = (): CorsightDwellTime[] => [
    {
        device_id: 1,
        device_name: 'Lobby Cam 1',
        room: 'Main Lobby',
        average_dwell_seconds: 272,
        start: dayjs().subtract(2, 'hour').toISOString(),
        end: dayjs().subtract(2, 'hour').add(34, 'second').toISOString(),
        duration_seconds: 34,
    },
    {
        device_id: 3,
        device_name: 'Auditorium Cam 1',
        room: 'Auditorium A',
        average_dwell_seconds: 545,
        start: dayjs().subtract(1, 'hour').toISOString(),
        end: dayjs().subtract(1, 'hour').add(2, 'minute').toISOString(),
        duration_seconds: 120,
    },
    {
        device_id: 4,
        device_name: 'Conf Room Cam 1',
        room: 'Conference Room 2',
        average_dwell_seconds: 189,
        start: dayjs().subtract(45, 'minute').toISOString(),
        end: dayjs().subtract(45, 'minute').add(51, 'second').toISOString(),
        duration_seconds: 51,
    },
];

// Deterministic pseudo-random so the dummy data is stable across reloads.
const createSeededRandom = (seed: number) => {
    let state = seed;
    return () => {
        state = (state * 9301 + 49297) % 233280;
        return state / 233280;
    };
};

const pickWeighted = <T>(rand: () => number, items: readonly T[], weights: number[]): T => {
    const r = rand() * weights.reduce((sum, w) => sum + w, 0);
    let acc = 0;
    for (let i = 0; i < items.length; i++) {
        acc += weights[i];
        if (r < acc) return items[i];
    }
    return items[items.length - 1];
};

const deviceForRoom = (room: string): CorsightDeviceOption => DUMMY_DEVICES.find((device) => device.room === room) ?? DUMMY_DEVICES[0];

const buildDummyEvents = (): CorsightRecentLog[] => {
    const roomNames = DUMMY_ROOMS.map((room) => room.name);
    const rand = createSeededRandom(469);

    const recent: CorsightRecentLog[] = [
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '20-29',
            direction: 'entered',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(4, 'minute').toISOString(),
            exit_at: null,
        },
        {
            id: '0',
            image: null,
            gender: 'female',
            age_range: '30-39',
            direction: 'entered',
            room: 'Auditorium A',
            timestamp: dayjs().subtract(11, 'minute').toISOString(),
            exit_at: null,
        },
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '40-49',
            direction: 'exited',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(19, 'minute').toISOString(),
            exit_at: dayjs().subtract(13, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'female',
            age_range: '20-29',
            direction: 'entered',
            room: 'Conference Room 2',
            timestamp: dayjs().subtract(26, 'minute').toISOString(),
            exit_at: null,
        },
        {
            id: '0',
            image: null,
            gender: 'unknown',
            age_range: '50+',
            direction: 'entered',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(33, 'minute').toISOString(),
            exit_at: null,
        },
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '30-39',
            direction: 'exited',
            room: 'Auditorium A',
            timestamp: dayjs().subtract(41, 'minute').toISOString(),
            exit_at: dayjs().subtract(35, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'female',
            age_range: '20-29',
            direction: 'entered',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(48, 'minute').toISOString(),
            exit_at: null,
        },
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '50+',
            direction: 'exited',
            room: 'Conference Room 2',
            timestamp: dayjs().subtract(57, 'minute').toISOString(),
            exit_at: dayjs().subtract(50, 'minute').toISOString(),
        },
    ];

    const historical: CorsightRecentLog[] = Array.from({ length: 50 }, () => {
        const enteredAt = dayjs().subtract(24 + rand() * 712, 'hour');
        const exited = rand() > 0.5;

        return {
            id: 0,
            image: null,
            gender: pickWeighted(rand, ['male', 'female', 'unknown'], [50, 45, 5]),
            age_range: pickWeighted(rand, AGE_RANGES, [42, 34, 16, 8]),
            direction: exited ? 'exited' : 'entered',
            room: pickWeighted(rand, roomNames, [5, 3, 2]),
            timestamp: enteredAt.toISOString(),
            exit_at: exited ? enteredAt.add(10 + rand() * 50, 'minute').toISOString() : null,
        };
    });

    return [...recent, ...historical]
        .sort((a, b) => dayjs(b.timestamp).valueOf() - dayjs(a.timestamp).valueOf())
        .map((event, index) => {
            const device = deviceForRoom(event.room ?? '');
            return { ...event, id: String(index + 1), device_id: device.id, device_name: device.name };
        });
};

const DUMMY_EVENTS = buildDummyEvents();
const DUMMY_DWELL = buildDummyDwell();

// The "Live" badge reads Disconnected once the newest record (server
// last_updated) is older than this — quiet periods and stale data both age it.
const LIVE_MAX_AGE_MS = 60_000;
const LIVE_TICK_MS = 15_000;

export type CorsightMonitoring = ReturnType<typeof useCorsightMonitoring>;

export function useCorsightMonitoring() {
    const events = ref<CorsightRecentLog[]>([]);
    const rooms = ref<CorsightRoom[]>([]);
    const devices = ref<CorsightDeviceOption[]>([]);
    const dwellTime = ref<CorsightDwellTime[]>([]);
    const isLoading = ref(false);
    const lastUpdated = ref<string | null>(null);

    // Toolbar filters — empty arrays mean "all".
    const selectedLocation = ref<string[]>([]);
    const selectedDevices = ref<number[]>([]);

    // A ticking clock so isLive re-evaluates without a new fetch.
    const now = ref(Date.now());
    let liveTicker: ReturnType<typeof setInterval> | null = null;

    // "Live" requires actual data: an empty database (fresh state) or a
    // fetch older than LIVE_MAX_AGE_MS both read as disconnected.
    const isLive = computed(() => {
        if (events.value.length === 0) return false;
        if (!lastUpdated.value) return false;
        return now.value - new Date(lastUpdated.value).getTime() <= LIVE_MAX_AGE_MS;
    });

    const startLiveTicker = () => {
        if (liveTicker) return;
        liveTicker = setInterval(() => {
            now.value = Date.now();
        }, LIVE_TICK_MS);
    };

    const stopLiveTicker = () => {
        if (!liveTicker) return;
        clearInterval(liveTicker);
        liveTicker = null;
    };

    // Toolbar filters
    const selectedRange = ref<CctvTimeRange>('today');

    const rangeCutoff = computed(() => {
        switch (selectedRange.value) {
            case 'today':
                return dayjs().startOf('day');
            case 'last_7_days':
                return dayjs().subtract(7, 'day');
            default:
                return dayjs().subtract(30, 'day');
        }
    });

    // Rooms covered by the selected cameras — the occupancy KPI follows the
    // device selection, not just the location selection.
    const selectedDeviceRooms = computed(() => new Set(devices.value.filter((device) => selectedDevices.value.includes(device.id)).map((device) => device.room)));

    const filteredEvents = computed(() =>
        events.value
            .filter((event) => dayjs(event.timestamp).isAfter(rangeCutoff.value))
            .filter((event) => selectedLocation.value.length === 0 || (event.room !== null && selectedLocation.value.includes(event.room)))
            .filter((event) => selectedDevices.value.length === 0 || selectedDevices.value.includes(event.device_id)),
    );

    const filteredRooms = computed(() =>
        rooms.value.filter(
            (room) =>
                (selectedLocation.value.length === 0 || selectedLocation.value.includes(room.name)) &&
                (selectedDevices.value.length === 0 || selectedDeviceRooms.value.has(room.name)),
        ),
    );

    const filteredDwell = computed(() =>
        dwellTime.value
            .filter((entry) => selectedLocation.value.length === 0 || selectedLocation.value.includes(entry.room))
            .filter((entry) => selectedDevices.value.length === 0 || selectedDevices.value.includes(entry.device_id)),
    );

    const peopleCount = computed<CorsightPeopleCount>(() => ({
        // Every row is an entry event (windowed by enter_at), so "entered"
        // is the row count itself; "exited" is the subset that has closed.
        // entered - exited ≈ current occupancy.
        entered: filteredEvents.value.length,
        exited: filteredEvents.value.filter((event) => event.direction === 'exited').length,
        occupancy: filteredRooms.value.reduce((sum, room) => sum + room.occupancy, 0),
        active_cameras: filteredRooms.value.reduce((sum, room) => sum + room.active_cameras, 0),
        total_cameras: filteredRooms.value.reduce((sum, room) => sum + room.total_cameras, 0),
    }));

    const demographics = computed<CorsightDemographics>(() => {
        const gender = { female: 0, male: 0, unknown: 0 };
        const ageCounts = new Map<string, number>();

        for (const event of filteredEvents.value) {
            gender[event.gender] += 1;

            if (event.age_range) {
                ageCounts.set(event.age_range, (ageCounts.get(event.age_range) ?? 0) + 1);
            }
        }

        return {
            total: filteredEvents.value.length,
            gender,
            age: AGE_RANGES.map((range) => ({ range, count: ageCounts.get(range) ?? 0 })),
        };
    });

    const overallDwellAverage = computed(() => {
        const list = filteredDwell.value;
        if (list.length === 0) return 0;
        return Math.round(list.reduce((sum, entry) => sum + entry.average_dwell_seconds, 0) / list.length);
    });

    const fetchCorsightData = (options?: { silent?: boolean }) => {
        const silent = options?.silent ?? false;

        nextTick(async () => {
            if (!silent) {
                isLoading.value = true;
            }

            if (USE_DUMMY) {
                events.value = DUMMY_EVENTS.map((event) => ({ ...event }));
                rooms.value = DUMMY_ROOMS.map((room) => ({ ...room }));
                devices.value = DUMMY_DEVICES.map((device) => ({ ...device }));
                dwellTime.value = DUMMY_DWELL.map((entry) => ({ ...entry }));
                lastUpdated.value = dayjs().toISOString();
                if (!silent) {
                    isLoading.value = false;
                }
                return;
            }

            try {
                const { data } = await axios.get(corsightData.url({ range: selectedRange.value }));
                events.value = data.recent_logs;
                rooms.value = data.rooms;
                devices.value = data.devices;
                dwellTime.value = data.dwell_time;
                // null when the window has zero records — keeps the badge
                // honest (Disconnected) and hides the "Last updated" stamp.
                lastUpdated.value = data.last_updated ?? null;
            } finally {
                if (!silent) {
                    isLoading.value = false;
                }
            }
        });
    };

    // The server scopes the dataset to the selected range, so a range change
    // needs a fresh fetch; the location filter stays client-side.
    watch(selectedRange, () => {
        fetchCorsightData();
    });

    return {
        rooms,
        devices,
        filteredRooms,
        filteredEvents,
        filteredDwell,
        peopleCount,
        demographics,
        overallDwellAverage,
        lastUpdated,
        selectedLocation,
        selectedDevices,
        selectedRange,
        isLoading,
        isLive,
        startLiveTicker,
        stopLiveTicker,
        fetchCorsightData,
    };
}
