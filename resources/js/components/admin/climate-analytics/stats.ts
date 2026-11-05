import type {
    ClimateFilters,
    ClimateReading,
    ClimateReadingFan,
    ClimateStat,
    ClimateStatBadge,
    ClimateStatBadgeKind,
    ClimateStats,
    ClimateStatsRange,
    ClimateTranslate,
} from '@/types';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import { CLIMATE_THRESHOLDS } from './config';

dayjs.extend(utc);
dayjs.extend(timezone);

const TIMEZONE = 'Asia/Manila';

const manila = (timestamp: string) => dayjs.utc(timestamp).tz(TIMEZONE);
const ms = (timestamp: string) => Date.parse(timestamp);
const formatTimestamp = (timestamp: string) => manila(timestamp).format('MMM D, h:mm A');

const isNumber = (value: unknown): value is number => typeof value === 'number' && Number.isFinite(value);

const average = (values: number[]): number | null => {
    if (!values.length) {
        return null;
    }

    return values.reduce((total, value) => total + value, 0) / values.length;
};

const median = (values: number[]): number | null => {
    if (!values.length) {
        return null;
    }

    const sorted = [...values].sort((a, b) => a - b);
    const middle = Math.floor(sorted.length / 2);

    return sorted.length % 2 === 0 ? (sorted[middle - 1] + sorted[middle]) / 2 : sorted[middle];
};

const fixed = (value: number | null, digits = 1): string => (value === null ? '–' : value.toFixed(digits));

const signed = (value: number, digits = 1): string => `${value > 0 ? '+' : ''}${value.toFixed(digits)}`;

const percent = (part: number, total: number): number | null => (total > 0 ? Math.round((part / total) * 100) : null);

const percentValue = (value: number | null): string => (value === null ? '–' : String(value));

const stat = (label: string, value: string, options: Omit<ClimateStat, 'label' | 'value'> = {}): ClimateStat => ({
    label,
    value,
    ...options,
});

const normalizeFan = (value: ClimateReadingFan | null | undefined): ClimateReadingFan | null => {
    if (value === null || value === undefined) {
        return null;
    }

    if (typeof value === 'string') {
        return value.toLowerCase() === 'auto' ? 'auto' : null;
    }

    return Number.isFinite(value) ? (value >= 4 || value <= 0 ? 'auto' : Math.min(3, Math.round(value))) : null;
};

const fanKey = (value: ClimateReadingFan): string => (value === 'auto' ? 'auto' : String(value));

const fanLevelKey = (value: number): 'low' | 'medium' | 'high' => (value < 1.5 ? 'low' : value < 2.5 ? 'medium' : 'high');

const resolveRange = (filters: ClimateFilters): ClimateStatsRange => {
    if (filters.start && filters.end) {
        const start = dayjs.tz(filters.start, TIMEZONE).startOf('day');
        const end = dayjs.tz(filters.end, TIMEZONE).endOf('day');

        return {
            days: Math.max(1, end.diff(start, 'day') + 1),
            endMs: Math.min(end.valueOf(), Date.now()),
        };
    }

    const hours = Math.max(1, filters.time ?? 24);

    return { days: hours / 24, endMs: Date.now() };
};

const medianIntervalMinutes = (readings: ClimateReading[]): number | null => {
    if (readings.length < 2) {
        return null;
    }

    const intervals = readings
        .slice(1)
        .map((reading, index) => (ms(reading.timestamp) - ms(readings[index].timestamp)) / 60_000)
        .filter((interval) => interval > 0);

    return median(intervals);
};

const onTimeHours = (readings: ClimateReading[], endMs: number): number => {
    let milliseconds = 0;

    readings.forEach((reading, index) => {
        if (reading.power !== 'on') {
            return;
        }

        const start = ms(reading.timestamp);
        const next = index + 1 < readings.length ? ms(readings[index + 1].timestamp) : endMs;
        const end = Math.min(next, endMs);

        if (end > start) {
            milliseconds += end - start;
        }
    });

    return milliseconds / 3_600_000;
};

const transitions = <Value>(values: Value[]): number =>
    values.reduce((count, value, index) => (index > 0 && value !== values[index - 1] ? count + 1 : count), 0);

const extremeBy = (
    readings: ClimateReading[],
    key: 'temperature' | 'setpoint',
    compare: (value: number, best: number) => boolean,
): ClimateReading | null =>
    readings.reduce<ClimateReading | null>((best, reading) => {
        const value = reading[key];

        return isNumber(value) && (!best || compare(value, best[key] as number)) ? reading : best;
    }, null);

const highestBy = (readings: ClimateReading[], key: 'temperature' | 'setpoint'): ClimateReading | null =>
    extremeBy(readings, key, (value, best) => value > best);

const lowestBy = (readings: ClimateReading[], key: 'temperature' | 'setpoint'): ClimateReading | null =>
    extremeBy(readings, key, (value, best) => value < best);

const badge = (kind: ClimateStatBadgeKind, text: string): ClimateStatBadge => ({ kind, text });

const comfortBadge = (share: number | null, t: ClimateTranslate): ClimateStatBadge | undefined => {
    if (share === null) {
        return undefined;
    }

    if (share >= CLIMATE_THRESHOLDS.comfortGood) {
        return badge('normal', t('page.climate_analytics.badge.comfortable'));
    }

    if (share >= CLIMATE_THRESHOLDS.comfortFair) {
        return badge('warning', t('page.climate_analytics.badge.mixed'));
    }

    return badge('danger', t('page.climate_analytics.badge.uncomfortable'));
};

const gapBadge = (gap: number | null, t: ClimateTranslate): ClimateStatBadge | undefined => {
    if (gap === null) {
        return undefined;
    }

    if (gap > CLIMATE_THRESHOLDS.gapBad) {
        return badge('danger', t('page.climate_analytics.badge.too_warm'));
    }

    if (gap > CLIMATE_THRESHOLDS.comfortBand) {
        return badge('warning', t('page.climate_analytics.badge.running_warm'));
    }

    if (gap < -CLIMATE_THRESHOLDS.comfortBand) {
        return badge('info', t('page.climate_analytics.badge.running_cool'));
    }

    return badge('normal', t('page.climate_analytics.badge.on_target'));
};

const buildSetpointAndActual = (onReadings: ClimateReading[], setpointChanges: number, t: ClimateTranslate): ClimateStat[] => {
    const actualAverage = average(onReadings.map((reading) => reading.temperature).filter(isNumber));
    const setpointAverage = average(onReadings.map((reading) => reading.setpoint).filter(isNumber));
    const gapValues = onReadings
        .filter((reading) => isNumber(reading.temperature) && isNumber(reading.setpoint))
        .map((reading) => (reading.temperature as number) - (reading.setpoint as number));
    const gapAverage = average(gapValues);
    const withinBand = percent(gapValues.filter((gap) => Math.abs(gap) <= CLIMATE_THRESHOLDS.comfortBand).length, gapValues.length);

    return [
        stat(t('page.climate_analytics.label.average_actual'), fixed(actualAverage), {
            unit: '°C',
            caption: `${t('page.climate_analytics.caption.while_on')} · ${t('page.climate_analytics.caption.measured_at_thermostat')}`,
        }),
        stat(t('page.climate_analytics.label.average_setpoint'), fixed(setpointAverage), {
            unit: '°C',
            caption: t('page.climate_analytics.caption.changes', { count: setpointChanges }),
            badge:
                setpointAverage !== null && setpointAverage < CLIMATE_THRESHOLDS.lowSetpoint
                    ? badge('danger', t('page.climate_analytics.badge.low_setpoint'))
                    : undefined,
        }),
        stat(t('page.climate_analytics.label.gap_to_setpoint'), gapAverage === null ? '–' : signed(gapAverage), {
            unit: '°C',
            caption: withinBand === null ? undefined : t('page.climate_analytics.caption.within_band', { percent: withinBand }),
            badge: gapBadge(gapAverage, t),
        }),
    ];
};

const buildFanSpeed = (readings: ClimateReading[], onReadings: ClimateReading[], t: ClimateTranslate): ClimateStat[] => {
    const numericFanValues = onReadings
        .map((reading) => normalizeFan(reading.fan_speed))
        .filter((value): value is number => typeof value === 'number');
    const fanAverage = average(numericFanValues);
    const highCount = numericFanValues.filter((value) => value === 3).length;
    const highShareRaw = numericFanValues.length ? highCount / numericFanValues.length : null;
    const highShare = percent(highCount, numericFanValues.length);
    const allFanValues = readings.map((reading) => normalizeFan(reading.fan_speed)).filter((value): value is ClimateReadingFan => value !== null);
    const fanChanges = transitions(allFanValues.map(fanKey));

    return [
        stat(t('page.climate_analytics.label.average_fan_speed'), fixed(fanAverage), {
            unit: fanAverage === null ? undefined : t('page.climate_analytics.fan.of_three'),
            caption:
                fanAverage === null
                    ? t('page.climate_analytics.caption.no_fan_readings')
                    : t(`page.climate_analytics.fan.${fanLevelKey(fanAverage)}`),
        }),
        stat(t('page.climate_analytics.label.time_on_high'), percentValue(highShare), {
            unit: highShare === null ? undefined : '%',
            caption: highShare === null ? t('page.climate_analytics.caption.no_fan_readings') : t('page.climate_analytics.caption.of_on_time'),
            badge:
                highShareRaw !== null && highShareRaw > CLIMATE_THRESHOLDS.highFanShare
                    ? badge('warning', t('page.climate_analytics.badge.struggling'))
                    : undefined,
        }),
        stat(t('page.climate_analytics.label.fan_changes'), allFanValues.length ? String(fanChanges) : '–', {
            caption: allFanValues.length ? t('page.climate_analytics.caption.in_range') : t('page.climate_analytics.caption.no_fan_readings'),
        }),
    ];
};

const buildPower = (readings: ClimateReading[], range: ClimateStatsRange, sparse: boolean, offline: boolean, t: ClimateTranslate): ClimateStat[] => {
    const powerReadings = readings.filter((reading) => reading.power);
    const lastPower = powerReadings.at(-1);
    const lastReading = readings.at(-1);
    const onCount = powerReadings.filter((reading) => reading.power === 'on').length;
    const powerChanges = transitions(powerReadings.map((reading) => reading.power));
    const changesPerDay = range.days > 0 ? powerChanges / range.days : powerChanges;
    const onHours = onTimeHours(readings, range.endMs);
    const lowSample = readings.length < CLIMATE_THRESHOLDS.lowSampleReadings;

    const runtime = lowSample
        ? stat(t('page.climate_analytics.label.runtime'), `${onCount} of ${readings.length}`, {
              caption: t('page.climate_analytics.caption.readings_on'),
          })
        : powerReadings.length === 0
          ? stat(t('page.climate_analytics.label.runtime'), '–', {
                caption: t('page.climate_analytics.caption.no_power_readings'),
            })
          : stat(t('page.climate_analytics.label.runtime'), range.days >= 7 ? fixed(onHours / range.days) : fixed(onHours), {
                unit: range.days >= 7 ? 'h/day' : 'h',
                caption: t('page.climate_analytics.caption.in_range'),
            });

    const lastKnownState = stat(
        t('page.climate_analytics.label.last_known_state'),
        lastPower?.power === 'on'
            ? t('page.climate_analytics.state.on')
            : lastPower?.power === 'off'
              ? t('page.climate_analytics.state.off')
              : t('state.no_data'),
        {
            caption:
                lastReading === undefined
                    ? undefined
                    : `${t('page.climate_analytics.caption.last_reading', { time: formatTimestamp(lastReading.timestamp) })}${
                          lastReading.mode ? ` · ${t('page.climate_analytics.caption.mode', { mode: lastReading.mode })}` : ''
                      }`,
            badge: offline ? badge('warning', t('page.climate_analytics.badge.offline')) : undefined,
        },
    );

    const onOffChanges = stat(t('page.climate_analytics.label.on_off_changes'), powerReadings.length ? fixed(changesPerDay) : '–', {
        unit: powerReadings.length ? '/day' : undefined,
        badge:
            !sparse && powerReadings.length > 0 && changesPerDay > CLIMATE_THRESHOLDS.frequentOnOffPerDay
                ? badge('warning', t('page.climate_analytics.badge.frequent_on_off'))
                : undefined,
    });

    return [lastKnownState, runtime, onOffChanges];
};

const buildTemperature = (readings: ClimateReading[], onReadings: ClimateReading[], t: ClimateTranslate): ClimateStat[] => {
    const lowest = lowestBy(readings, 'temperature');
    const highest = highestBy(readings, 'temperature');
    const averageOnSetpoint = average(onReadings.map((reading) => reading.setpoint).filter(isNumber));
    const highestSetpoint = highest && isNumber(highest.setpoint) ? highest.setpoint : averageOnSetpoint;
    const comfortPairs = onReadings
        .filter((reading) => isNumber(reading.temperature) && isNumber(reading.setpoint))
        .map((reading) => (reading.temperature as number) - (reading.setpoint as number));
    const comfortInBand = comfortPairs.filter((gap) => Math.abs(gap) <= CLIMATE_THRESHOLDS.comfortBand).length;
    const comfortShare = comfortPairs.length ? comfortInBand / comfortPairs.length : null;
    const comfortPercent = percent(comfortInBand, comfortPairs.length);

    return [
        stat(t('page.climate_analytics.label.lowest_temperature'), lowest ? fixed(lowest.temperature as number) : '–', {
            unit: '°C',
            caption: lowest ? t('page.climate_analytics.caption.at', { time: formatTimestamp(lowest.timestamp) }) : undefined,
        }),
        stat(t('page.climate_analytics.label.highest_temperature'), highest ? fixed(highest.temperature as number) : '–', {
            unit: '°C',
            caption: highest ? t('page.climate_analytics.caption.at', { time: formatTimestamp(highest.timestamp) }) : undefined,
            badge:
                highest &&
                highest.power === 'on' &&
                isNumber(highest.temperature) &&
                isNumber(highestSetpoint) &&
                highest.temperature - highestSetpoint > CLIMATE_THRESHOLDS.gapBad
                    ? badge('warning', t('page.climate_analytics.badge.above_setpoint'))
                    : undefined,
        }),
        stat(t('page.climate_analytics.label.time_in_comfort_band'), percentValue(comfortPercent), {
            unit: comfortPairs.length ? '%' : undefined,
            caption: comfortPairs.length ? t('page.climate_analytics.caption.within_comfort') : undefined,
            badge: comfortBadge(comfortShare, t),
        }),
    ];
};

const buildSetpoint = (readings: ClimateReading[], range: ClimateStatsRange, setpointChanges: number, t: ClimateTranslate): ClimateStat[] => {
    const setpointCount = readings.filter((reading) => isNumber(reading.setpoint)).length;
    const lowest = lowestBy(readings, 'setpoint');
    const highest = highestBy(readings, 'setpoint');
    const changesPerDay = range.days > 0 ? setpointChanges / range.days : setpointChanges;

    return [
        stat(t('page.climate_analytics.label.lowest_setpoint'), lowest ? fixed(lowest.setpoint as number) : '–', {
            unit: '°C',
            caption: lowest ? t('page.climate_analytics.caption.at', { time: formatTimestamp(lowest.timestamp) }) : undefined,
            badge:
                lowest && isNumber(lowest.setpoint) && lowest.setpoint < CLIMATE_THRESHOLDS.lowSetpoint
                    ? badge('danger', t('page.climate_analytics.badge.low'))
                    : undefined,
        }),
        stat(t('page.climate_analytics.label.highest_setpoint'), highest ? fixed(highest.setpoint as number) : '–', {
            unit: '°C',
            caption: highest ? t('page.climate_analytics.caption.at', { time: formatTimestamp(highest.timestamp) }) : undefined,
        }),
        stat(t('page.climate_analytics.label.setpoint_changes'), setpointCount ? String(setpointChanges) : '–', {
            caption: setpointCount
                ? t('page.climate_analytics.caption.per_day', { value: fixed(changesPerDay) })
                : t('page.climate_analytics.caption.no_setpoint_readings'),
            badge:
                setpointCount > 0 && changesPerDay > CLIMATE_THRESHOLDS.frequentSetpointChangesPerDay
                    ? badge('warning', t('page.climate_analytics.badge.frequent'))
                    : undefined,
        }),
    ];
};

export const buildClimateStats = (source: ClimateReading[], filters: ClimateFilters, t: ClimateTranslate, deviceOffline?: boolean): ClimateStats => {
    console.table(source.map((r) => ({ t: r.timestamp, temp: r.temperature, power: r.power })));

    const readings = [...source].sort((a, b) => ms(a.timestamp) - ms(b.timestamp));
    const onReadings = readings.filter((reading) => reading.power === 'on');
    const range = resolveRange(filters);
    const medianInterval = medianIntervalMinutes(readings);
    const sparse = medianInterval === null ? readings.length < 2 : medianInterval > CLIMATE_THRESHOLDS.minReadingIntervalMinutes;
    const lastReading = readings.at(-1);
    const offline =
        deviceOffline ??
        (lastReading !== undefined && Date.now() - ms(lastReading.timestamp) >= CLIMATE_THRESHOLDS.minReadingIntervalMinutes * 60_000);
    const setpointChanges = transitions(readings.filter((reading) => isNumber(reading.setpoint)).map((reading) => reading.setpoint as number));

    return {
        setpointAndActual: buildSetpointAndActual(onReadings, setpointChanges, t),
        fanSpeed: buildFanSpeed(readings, onReadings, t),
        power: buildPower(readings, range, sparse, offline, t),
        temperature: buildTemperature(readings, onReadings, t),
        setpoint: buildSetpoint(readings, range, setpointChanges, t),
    };
};
