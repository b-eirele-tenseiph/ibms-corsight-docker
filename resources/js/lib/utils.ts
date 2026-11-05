import { useAppearance } from '@/composables/useAppearance';
import { Time } from '@internationalized/date';
import { clsx, type ClassValue } from 'clsx';
import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import relativeTime from 'dayjs/plugin/relativeTime';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import { twMerge } from 'tailwind-merge';
import { computed } from 'vue';

dayjs.extend(timezone);
dayjs.extend(utc);
dayjs.extend(customParseFormat);
dayjs.extend(relativeTime);

// ═══════════════════════════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Merges and deduplicates Tailwind CSS class names intelligently.
 *
 * @param inputs Class names, objects, or arrays to merge
 * @returns A single merged class string
 *
 */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function useTooltipContentColor() {
    const { appearance } = useAppearance();
    const isDark = computed(() => appearance.value === 'dark');

    const tooltipStyles = computed(() => ({
        bg: isDark.value ? 'hsl(48, 96%, 12%)' : 'oklch(98.7% 0.026 102.212)',
        text: isDark.value ? 'hsl(48, 96%, 65%)' : '#78350f',
        strong: isDark.value ? 'hsl(48, 96%, 70%)' : '#92400e',
        border: isDark.value ? 'rgba(217, 119, 6, 0.4)' : 'rgba(217, 119, 6, 0.2)',
    }));

    return { tooltipStyles };
}

// ═══════════════════════════════════════════════════════════════════════════
// DATE & TIME UTILITIES
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Formats dates in a consistent 12-hour format.
 *
 * @returns An object with a `formatDate` function
 *
 */
export function useDateFormat() {
    const formatDate = (date: string): string => {
        return dayjs(date).format('DD-MM-YYYY hh:mm A');
    };

    return { formatDate };
}

/**
 * Converts a date to a human-readable relative time string.
 *
 * @param date Date object or ISO string
 * @returns Relative time string (ex. 2 days ago)
 *
 */
export function formatRelativeTime(date: Date | string): string {
    return dayjs.utc(date).tz('Asia/Manila').fromNow();
}

/**
 * Formats a date string into a long, human-friendly format.
 *
 * @param dateString ISO date string or any valid date format
 * @returns Formatted string (ex. February 9, 2026 2:30 PM) or 'Invalid date' on error
 *
 */
export function formatReadableDate(dateString: string): string {
    if (!dateString) return '';

    const date = dayjs(dateString);
    if (!date.isValid()) return 'Invalid date';

    return date.format('MMMM D, YYYY h:mm A');
}

/**
 * Parses a 24-hour time string into a Time object.
 *
 * @param timeStr Time string in "HH:mm" format (e.g., "14:30")
 * @returns Time object with hour and minute properties
 */
export function parseTimeString(timeStr: string) {
    const [hour, minute] = timeStr.split(':').map(Number);
    return new Time(hour, minute);
}

/**
 * Converts a Time object to a 24-hour format string.
 *
 * @param time Time object to format
 * @returns Zero-padded time string in "HH:mm" format
 *
 */
export function formatTime(time: Time): string {
    return `${time.hour.toString().padStart(2, '0')}:${time.minute.toString().padStart(2, '0')}`;
}

/**
 * Converts a UTC datetime to local date and time parts.
 *
 * Useful for populating separate date and time input fields.
 *
 * @param utcString UTC datetime string (e.g., "2025-07-23 16:00:00")
 * @returns Object with separate date and time strings
 *
 */
export const fromUtcToLocalParts = (utcString: string): { date: string; time: string } => {
    if (!utcString) return { date: '', time: '' };

    const localDate = dayjs.utc(utcString).local();

    return {
        date: localDate.format('YYYY-MM-DD'),
        time: localDate.format('HH:mm'),
    };
};

/**
 * Safely parses a days field which may arrive in multiple formats:
 * - JSON-encoded string:    "[\"Wednesday\",\"Sunday\"]"
 * - Plain comma-separated: "Wednesday,Sunday" (legacy data)
 *
 * @param days
 * @returns
 */
export function parseDays(days: string | null | undefined): string[] {
    if (!days) return [];

    try {
        const parsed = JSON.parse(days);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return days
            .split(',')
            .map((d) => d.trim())
            .filter(Boolean);
    }
}

/**
 * Formats the trigger display text and icon for a scene.
 *
 * @param scene
 * @param labels
 * @returns
 */
export function formatScheduleDisplay(
    scene: { scheduled: boolean; time?: string | null; days?: string | null },
    labels: {
        manual: string;
        everyday: string;
        scheduled: string;
    },
): { text: string; icon: string } {
    if (!scene.scheduled) {
        return {
            text: labels.manual,
            icon: 'mdi:play-circle-outline',
        };
    }

    const formattedTime = scene.time ? dayjs.utc(scene.time, 'HH:mm:ss').tz(dayjs.tz.guess()).format('hh:mm A') : '';

    const days = parseDays(scene.days);

    const formattedDays =
        days.length === 0 ? labels.scheduled : days.length === 7 ? labels.everyday : days.map((day) => day.substring(0, 3)).join(', ');

    return {
        text: formattedTime ? `${formattedTime} (${formattedDays})` : formattedDays,
        icon: 'mdi:clock-outline',
    };
}

// ═══════════════════════════════════════════════════════════════════════════
// NUMBER UTILITIES
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Rounds a number to the nearest multiple of a given step.
 *
 * @param value - The number to round
 * @param step - The step size to round to
 * @returns The rounded value
 *
 */
export const roundToStep = (value: number, step: number) => Math.round(value / step) * step;
