export interface CorsightRoom {
    name: string;
    active_cameras: number;
    total_cameras: number;
    occupancy: number;
}

export interface CorsightPeopleCount {
    entered: number;
    exited: number;
    occupancy: number;
    active_cameras: number;
    total_cameras: number;
}

export interface CorsightRecentLog {
    id: string;
    image: string | null;
    gender: 'male' | 'female' | 'unknown';
    age_range: '20-29' | '30-39' | '40-49' | '50+' | null;
    direction: 'entered' | 'exited';
    room: string | null;
    timestamp: string;
}

export interface CorsightDwellTime {
    room: string;
    average_dwell_seconds: number;
    start: string;
    end: string;
    duration_seconds: number;
}

export interface CorsightDemographics {
    total: number;
    gender: {
        female: number;
        male: number;
        unknown: number;
    };
    age: {
        range: string;
        count: number;
    }[];
}

export type CctvTimeRange = 'today' | '7d' | '30d';
