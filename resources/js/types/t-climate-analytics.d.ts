export type ChartDataset = {
    label: string;
    borderColor: string;
    backgroundColor: string;
    data: Array<number | null>;
};

export type ChartPayload = {
    labels: string[];
    datasets: ChartDataset[];
};

export type ClimateRoomArea = {
    id: number;
    name: string;
    last_uplink?: string | null;
};

export type ClimateRoom = {
    id: number;
    number: string;
    name: string;
    room_areas: ClimateRoomArea[];
};

export type ClimateResolution = 'hour' | 'six_hour' | 'day';

export type ClimateFilters = {
    time: number | null;
    start: string | null;
    end: string | null;
    room: string;
    room_area: string;
    resolution: ClimateResolution;
};

export type ClimateChipTone = 'secondary' | 'success' | 'info' | 'warning' | 'error';

export type ClimateChipBadge = {
    label: string;
    icon?: string;
    tone?: ClimateChipTone;
};

export type ClimateChip = {
    label: string;
    value: string;
    unit?: string;
    suffix?: string;
    badge?: ClimateChipBadge;
};

export type ClimateStatBadgeKind = 'normal' | 'warning' | 'danger' | 'info';

export type BadgeMeta = {
    priority: number;
    icon: string;
    class: string;
};

export type ClimateStatBadge = {
    kind: ClimateStatBadgeKind;
    text: string;
    hint?: string;
};

export type ClimateStat = {
    label: string;
    value: string;
    unit?: string;
    caption?: string;
    badge?: ClimateStatBadge;
    color?: string;
};

export type ClimateStats = {
    setpointAndActual: ClimateStat[];
    fanSpeed: ClimateStat[];
    power: ClimateStat[];
    temperature: ClimateStat[];
    setpoint: ClimateStat[];
};

export type ClimateStatsRange = {
    days: number;
    endMs: number;
};

export type ClimateTranslate = (key: string, params?: Record<string, unknown>) => string;

export type ClimateReadingPower = 'on' | 'off';

export type ClimateReadingFan = number | 'auto';

export type ClimateReading = {
    timestamp: string;
    temperature?: number | null;
    setpoint?: number | null;
    power?: ClimateReadingPower | null;
    fan_speed?: ClimateReadingFan | null;
    mode?: string | null;
    setpoint_source?: string | null;
};

export type ClimateReadingsPayload = {
    readings: ClimateReading[];
};
