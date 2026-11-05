type UsageMetric = {
    total: number;
    percentage: number;
};

type ActiveMetric = UsageMetric & { active: number };

export interface MetricsData {
    today_usage: UsageMetric;
    weekly_avg: UsageMetric;
    monthly_total: UsageMetric;
    active_meter: ActiveMetric;
}

export interface MetricConfig {
    label: string;
    icon: string;
    description: string;
    unit?: string;
}

interface TopWaterMeterConsumer extends Pick<WaterMeterData, 'meter' | 'location' | 'usage'> {
    id: number;
}

interface WaterMeterStatus {
    id: number;
    status: 'active' | 'offline';
}

interface WaterMeterLocation {
    id: number;
    location: string;
}

interface WaterMeterData {
    meter: string;
    location: string;
    usage: number;
    today_usage: number;
    flow_rate: number;
    estimated_bill: number;
    last_uplink: string;
}
