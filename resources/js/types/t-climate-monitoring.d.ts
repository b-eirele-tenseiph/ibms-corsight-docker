import { QueryParams } from './shared';
import { LorawanSwitch } from './t-lorawan-devices';
import { SwitchBotDeviceData } from './t-switch-bot-device';
import { SwitchBotRemoteData } from './t-switch-bot-remote';

interface DataPoint {
    unit: string | null;
    value: string | number;
}

export interface SwitchProps {
    switches: PaginatedResponse<LorawanSwitch>;
    queryParams: QueryParams;
}

export interface PlugProps {
    appliances: PaginatedResponse<SwitchBotDeviceData>;
    queryParams: QueryParams;
}

export interface AirconProps {
    aircons: PaginatedResponse<SwitchBotRemoteData>;
    queryParams: QueryParams;
}

export interface AirconData extends SwitchBotRemoteData {
    id: string | number;
    model?: string;
    tempHasChanged?: boolean;
    sourceType?: string;
}

export interface ThermostatContextData {
    [key: string]: {
        unit: string | null;
        value: string | number;
    };
}
