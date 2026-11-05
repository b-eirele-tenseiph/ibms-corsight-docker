import { ChartSeries } from './shared';

type Meters = {
    id: string;
    name: string;
    dev_eui: string;
};

type ReportFilters = {
    start: string;
    end: string;
    meters: Array<Meters>;
};

export interface ReportHeader {
    meters?: Array<Meters>;
    filters: ReportFilters;
}

export interface ElectricMonitoringReportProps {
    jobId: string;
    meters?: Array<Meters>;
    filters: ReportFilters;
    preloadedData: Record<string, any>;
}

export interface EnergySummary {
    total_consumption: number;
    total_cost: number;
    carbon_footprint: number;
}

export interface ConsumptionComparison {
    avg_daily: {
        changes: number;
        current: number;
        prev: number;
    };
    consumption: {
        changes: number;
        current: number;
        prev: number;
    };
}

export interface CarbonMonthlyBreakdown {
    month: string;
    consumption: number;
    co2_emissions: number;
    excess_emissions: number;
    potential_savings: number;
    daily_average: number;
}

export interface CarbonSummary {
    total_co2_emissions: number;
    carbon_intensity: number;
    previous_period: number;
    reduction_percent: number;
}

export type CarbonTrend = ChartSeries & {};
