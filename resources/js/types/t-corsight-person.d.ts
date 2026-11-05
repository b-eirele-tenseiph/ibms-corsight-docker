import { QueryParams } from './shared';

export interface CorsightPersonWatchlistMatch {
    name: string;
    type: string | null;
    outcome: string | null;
    severity: string | null;
    repeated_alert: boolean;
}

export interface CorsightPersonSessionData {
    id: string;
    status: 'in' | 'exited' | 'abandoned';
    enter_at: string;
    exit_at: string | null;
    dwell_seconds: number | null;
    gender: string | null;
    age_group: string | null;
    face_score: number | null;
    image: string | null;
    security_flag: boolean;
    poi_name: string | null;
    poi_confidence: number | null;
    watchlist_matches: CorsightPersonWatchlistMatch[] | null;
    camera: string | null;
    role: string | null;
    room: {
        id: string | null;
        name: string | null;
    };
    room_area: {
        id: string | null;
        name: string | null;
    };
}

export interface CorsightPersonCounters {
    in_now: number;
    security: number;
    entries: number;
    exits: number;
}

export interface CorsightPersonDevice {
    id: string;
    name: string;
    role: string;
}

export interface CorsightPersonProps {
    people: PaginatedResponse<CorsightPersonSessionData>;
    counters: CorsightPersonCounters;
    devices: CorsightPersonDevice[];
    filters: QueryParams;
}
