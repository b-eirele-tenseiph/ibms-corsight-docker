import type { BadgeMeta, ClimateChipTone, ClimateStatBadge, ClimateStatBadgeKind, ClimateTranslate } from '@/types';

/**
 * Tuning values for the Climate Analytics stat cards.
 */
export const CLIMATE_THRESHOLDS = {
    comfortBand: 1.0,
    lowSetpoint: 22,
    gapBad: 2.0,
    highFanShare: 0.5,
    frequentOnOffPerDay: 4,
    frequentSetpointChangesPerDay: 3,
    comfortGood: 0.8,
    comfortFair: 0.5,
    lowSampleReadings: 12,
    minReadingIntervalMinutes: 30,
} as const;

/**
 * Badge Display
 */
export const BADGE_META: Record<ClimateStatBadgeKind, BadgeMeta> = {
    normal: { priority: 0, icon: 'lucide:check', class: 'bg-success-bg text-success-text' },
    warning: { priority: 2, icon: 'lucide:triangle-alert', class: 'bg-warning-bg text-warning-text' },
    danger: { priority: 3, icon: 'lucide:x', class: 'bg-error-bg text-error-text' },
    info: { priority: 1, icon: 'lucide:arrow-down', class: 'bg-info-bg text-info-text' },
};

export const badge = (value: ClimateChipTone | ClimateStatBadgeKind) => {
    let kind: ClimateStatBadgeKind;

    switch (value) {
        case 'success':
            kind = 'normal';
            break;
        case 'error':
            kind = 'danger';
            break;
        case 'secondary':
            kind = 'info';
            break;
        default:
            kind = value;
    }

    return { kind, ...BADGE_META[kind] };
};

export const statBadge = (kind: ClimateStatBadgeKind, text: string): ClimateStatBadge => ({ kind, text });

export const comfortBadge = (share: number | null, t: ClimateTranslate): ClimateStatBadge | undefined => {
    if (share === null) {
        return undefined;
    }

    if (share >= CLIMATE_THRESHOLDS.comfortGood) {
        return statBadge('normal', t('page.climate_analytics.badge.comfortable'));
    }

    if (share >= CLIMATE_THRESHOLDS.comfortFair) {
        return statBadge('warning', t('page.climate_analytics.badge.mixed'));
    }

    return statBadge('danger', t('page.climate_analytics.badge.uncomfortable'));
};

export const gapBadge = (gap: number | null, t: ClimateTranslate): ClimateStatBadge | undefined => {
    if (gap === null) {
        return undefined;
    }

    if (gap > CLIMATE_THRESHOLDS.gapBad) {
        return statBadge('danger', t('page.climate_analytics.badge.too_warm'));
    }

    if (gap > CLIMATE_THRESHOLDS.comfortBand) {
        return statBadge('warning', t('page.climate_analytics.badge.running_warm'));
    }

    if (gap < -CLIMATE_THRESHOLDS.comfortBand) {
        return statBadge('info', t('page.climate_analytics.badge.running_cool'));
    }

    return statBadge('normal', t('page.climate_analytics.badge.on_target'));
};
