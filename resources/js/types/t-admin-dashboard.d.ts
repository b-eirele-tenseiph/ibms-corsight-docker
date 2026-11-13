import { GridPosition } from './shared';
import { ElectricMeterData } from './t-electric-monitoring';

type Count = { total: number | ComputedRef<number>; param: number | ComputedRef<number> };

type Meter = {
    id: string;
    name: string;
    total_kwh: number;
    carbon_kg: number;
};

type SeriesPoint = {
    point: [string, number | 'No Data'];
    hasGap: boolean;
    data_status: 'complete' | 'gap_start' | 'gap_end';
    gap_info: GapInfo | null;
};

type DataPoint<T extends number | 'No Data' = number> = [string, T];

type MonthlyCost = {
    cost: number;
    measuredCost: number;
    unattributedCost: number;
};

export type MeterPhase = { label: string };

export type LocationWithMeter = { id: string; name: string };

export type DeviceTabType = 'aircon' | 'switches' | 'appliances';

export type SeriesItem = ChartSeries & {
    id: string;
};

export interface GapInfo {
    gap_start: string;
    gap_end: string;
    gap_duration_hours: number;
}

interface BaseEnergyData<T extends number | 'No Data' = number> {
    name: string;
    latest_data: string;
    data: DataPoint<T>[];
}

export interface EnvironmentSensorData {
    title: string;
    unit: string;
    threshold: {
        min: number;
        max: number;
    };
    seriesData: any;
}

export interface DeviceInfo {
    label: string;
    icon: string;
    badge?: string;
    link?: string;
}

export interface DeviceStatus {
    id: string;
    name: string;
    status: string;
    last_seen: string | Date;
    icon: string;
}

export interface DeviceCount {
    accounts: Count;
    gateways: Count;
    devices: Count;
    rooms: Count;
}

export interface ElectricityConsumption {
    total: number;
    name: string;
    data: [string, number][];
}

export interface CostData {
    current_month?: MonthlyCost;
    last_month?: MonthlyCost;
}

export interface HourlyEnergyData extends BaseEnergyData<number | 'No Data'> {
    id: string;
    alias: string;
    series: SeriesPoint[];
}

export interface MonthlyEnergyData extends Omit<BaseEnergyData, 'data'> {
    data: {
        month: string;
        consumption: number | null;
        confirmed_consumption: number | null;
        unattributed_consumption: number | null;
        has_unattributed: boolean;
    }[];
}

export interface RecentActivity {
    id: number;
    type: 'information' | 'warning' | 'error';
    description: string;
    last_seen: string;
    icon: string;
}

export interface CarbonFootPrintData {
    month: string;
    emission_factor: number;
    target_kg: string;
    total_carbon_kg: number;
    prev_total_carbon_kg: number;
    carbon_kg_change: number;
    meters: Array<Meter>;
}

export interface PowerFactorData {
    id: string;
    name: string;
    power_factor: number;
}

export interface ElectricMeterListData {
    id: string;
    location: string;
    current: number;
    voltage: number;
    last_updated_at: string;
    name: string;
    data: [number | string | Date, number][];
}

export interface AdminDashboardData {
    electricMeter: Array<ElectricMeterData>;
    rooms: {
        positions: {
            column_number: string;
            context: GridPosition;
        }[];
    };
    location_with_meters?: LocationWithMeter[];
}

export interface AdminMonitoringPositionLayout {
    electricityCostPosition: GridPosition;
    deviceCountPosition: GridPosition;
    roomPosition: GridPosition;
    deviceActivePosition: GridPosition;
    deviceStatusPosition: GridPosition;
    recentActivityPosition: GridPosition;
    environmentDataPosition: GridPosition;
    peopleCountPosition: GridPosition;
}

export interface AdminEnergyPositionLayout {
    electricMeterPosition: GridPosition;
    carbonTrackerPosition: GridPosition;
    powerFactorPosition: GridPosition;
    dailyEnergyCostPosition: GridPosition;
    hourlyConsumptionPosition: GridPosition;
    monthlyConsumptionPosition: GridPosition;
}
