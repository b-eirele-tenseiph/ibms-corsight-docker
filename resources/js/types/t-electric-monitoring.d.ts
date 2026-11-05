type ElectricMeterData = {
    id?: string;
    name?: string;
    room_name?: string;
    created_at?: string;
    updated_at?: string;
    consumption?: any;
    power?: any;
    voltage?: any;
    last_uplink?: any;
    data?: any;
};

type ElectricMeter = {
    id: string;
    name: string;
    status: string;
    device_id: string;
    updated_at: string;
};

type SeriesData = {
    id: string;
    unit: string;
    title: string;
    series: seriesDataPoint[];
    current_unbalance?: number;
    voltage_unbalance?: number;
    totalConsumption?: number;
};

type seriesDataPoint = {
    id: string;
    name: string;
    data: [string, number][];
};

interface ElectricMeterGraphData {
    data: SeriesData;
    isLoading: boolean;
    load: object;
    control: any;
    controls: string[];
    currentValue: string;
    filterHours: (event: any) => void;
}

export interface ElectricMeterProps {
    electric_meter: Array<ElectricMeterData>;
    filters: Record<string, any>;
}

export interface ElectricMeterReportProps {
    electricMeter: Array<{ id: any; name: string }>;
}
