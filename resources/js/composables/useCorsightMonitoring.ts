import { corsightData } from '@/routes/admin/dashboard';
import { CctvTimeRange, CorsightDemographics, CorsightDwellTime, CorsightPeopleCount, CorsightRecentLog, CorsightRoom } from '@/types';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, ref } from 'vue';

// Set to true to fall back to the bundled dummy data while the backend is unavailable.
const USE_DUMMY = false;

const AGE_RANGES = ['20-29', '30-39', '40-49', '50+'] as const;

const DUMMY_ROOMS: CorsightRoom[] = [
    { name: 'Main Lobby', active_cameras: 2, total_cameras: 2, occupancy: 6 },
    { name: 'Auditorium A', active_cameras: 1, total_cameras: 1, occupancy: 4 },
    { name: 'Conference Room 2', active_cameras: 1, total_cameras: 2, occupancy: 6 },
];

const buildDummyDwell = (): CorsightDwellTime[] => [
    {
        room: 'Main Lobby',
        average_dwell_seconds: 272,
        start: dayjs().subtract(2, 'hour').toISOString(),
        end: dayjs().subtract(2, 'hour').add(34, 'second').toISOString(),
        duration_seconds: 34,
    },
    {
        room: 'Auditorium A',
        average_dwell_seconds: 545,
        start: dayjs().subtract(1, 'hour').toISOString(),
        end: dayjs().subtract(1, 'hour').add(2, 'minute').toISOString(),
        duration_seconds: 120,
    },
    {
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
        },
        {
            id: '0',
            image: null,
            gender: 'female',
            age_range: '30-39',
            direction: 'entered',
            room: 'Auditorium A',
            timestamp: dayjs().subtract(11, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '40-49',
            direction: 'exited',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(19, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'female',
            age_range: '20-29',
            direction: 'entered',
            room: 'Conference Room 2',
            timestamp: dayjs().subtract(26, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'unknown',
            age_range: '50+',
            direction: 'entered',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(33, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '30-39',
            direction: 'exited',
            room: 'Auditorium A',
            timestamp: dayjs().subtract(41, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'female',
            age_range: '20-29',
            direction: 'entered',
            room: 'Main Lobby',
            timestamp: dayjs().subtract(48, 'minute').toISOString(),
        },
        {
            id: '0',
            image: null,
            gender: 'male',
            age_range: '50+',
            direction: 'exited',
            room: 'Conference Room 2',
            timestamp: dayjs().subtract(57, 'minute').toISOString(),
        },
    ];

    const historical: CorsightRecentLog[] = Array.from({ length: 50 }, () => ({
        id: 0,
        image: null,
        gender: pickWeighted(rand, ['male', 'female', 'unknown'], [50, 45, 5]),
        age_range: pickWeighted(rand, AGE_RANGES, [42, 34, 16, 8]),
        direction: pickWeighted(rand, ['entered', 'exited'], [50, 50]),
        room: pickWeighted(rand, roomNames, [5, 3, 2]),
        timestamp: dayjs()
            .subtract(24 + rand() * 712, 'hour')
            .toISOString(),
    }));

    return [...recent, ...historical]
        .sort((a, b) => dayjs(b.timestamp).valueOf() - dayjs(a.timestamp).valueOf())
        .map((event, index) => ({ ...event, id: String(index + 1) }));
};

const DUMMY_EVENTS = buildDummyEvents();
const DUMMY_DWELL = buildDummyDwell();

export function useCorsightMonitoring() {
    const events = ref<CorsightRecentLog[]>([]);
    const rooms = ref<CorsightRoom[]>([]);
    const dwellTime = ref<CorsightDwellTime[]>([]);
    const isLoading = ref(false);
    const lastUpdated = ref<string | null>(null);

    // Toolbar filters
    const selectedLocation = ref('all');
    const selectedRange = ref<CctvTimeRange>('today');

    const rangeCutoff = computed(() => {
        switch (selectedRange.value) {
            case 'today':
                return dayjs().startOf('day');
            case '7d':
                return dayjs().subtract(7, 'day');
            default:
                return dayjs().subtract(30, 'day');
        }
    });

    const isLocationFiltered = computed(() => selectedLocation.value !== 'all');

    const filteredEvents = computed(() =>
        events.value
            .filter((event) => dayjs(event.timestamp).isAfter(rangeCutoff.value))
            .filter((event) => !isLocationFiltered.value || event.room === selectedLocation.value),
    );

    const filteredRooms = computed(() => rooms.value.filter((room) => !isLocationFiltered.value || room.name === selectedLocation.value));

    const filteredDwell = computed(() => dwellTime.value.filter((entry) => !isLocationFiltered.value || entry.room === selectedLocation.value));

    const peopleCount = computed<CorsightPeopleCount>(() => ({
        entered: filteredEvents.value.filter((event) => event.direction === 'entered').length,
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
                dwellTime.value = DUMMY_DWELL.map((entry) => ({ ...entry }));
                lastUpdated.value = dayjs().toISOString();
                if (!silent) {
                    isLoading.value = false;
                }
                return;
            }

            try {
                const { data } = await axios.get(corsightData.url());
                events.value = data.recent_logs;
                rooms.value = data.rooms;
                dwellTime.value = data.dwell_time;
                lastUpdated.value = data.last_updated ?? dayjs().toISOString();
            } finally {
                if (!silent) {
                    isLoading.value = false;
                }
            }
        });
    };

    return {
        rooms,
        filteredRooms,
        filteredEvents,
        filteredDwell,
        peopleCount,
        demographics,
        overallDwellAverage,
        lastUpdated,
        selectedLocation,
        selectedRange,
        isLoading,
        fetchCorsightData,
    };
}
