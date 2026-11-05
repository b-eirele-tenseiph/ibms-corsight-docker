<script setup lang="ts">
import { ModalShowForm } from '@/components/shared/modals';
import { Badge } from '@/components/ui/badge';
import { show as personShow } from '@/routes/admin/management/corsight/people';
import { CorsightPersonSessionData } from '@/types';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    show: boolean;
    person?: Pick<CorsightPersonSessionData, 'id'>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const { t, te } = useI18n();

const detail = reactive({
    form: {
        appearance_id: '' as string,
        camera_id: '' as string,
        status: '' as string,
        enter_at: '' as string,
        exit_at: null as string | null,
        dwell_seconds: null as number | null,
        last_seen_at: '' as string,
        gender: null as string | null,
        age_group: null as string | null,
        face_score: null as number | null,
        image: null as string | null,
        security_flag: false as boolean,
        watchlist_matches: null as Array<{
            name: string;
            type: string | null;
            outcome: string | null;
            severity: string | null;
            repeated_alert: boolean;
        }> | null,
        poi_name: null as string | null,
        poi_confidence: null as number | null,
        liveness: null as string | null,
        mask_outcome: null as string | null,
        event_count: 0 as number,
        camera: null as string | null,
        role: null as string | null,
        room: { id: null as string | null, name: null as string | null },
        room_area: { id: null as string | null, name: null as string | null },
        trail: [] as Array<{ received_at: string; trigger: number | null; event_id: string | null }>,
    },
    processData: () => {
        axios.get(personShow({ session: props.person!.id }).url).then((response) => {
            Object.assign(detail.form, response.data);
        });
    },
}) as any;

const formatDwell = (seconds: number | null) => {
    if (seconds === null) return '-';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    if (hours > 0) return `${hours}h ${minutes}m`;
    if (minutes > 0) return `${minutes}m ${secs}s`;
    return `${secs}s`;
};

// Maps Fortify's appearance trigger codes to readable trail labels
const triggerLabel = (trigger: number | null): string => {
    switch (trigger) {
        case 0:
            return t('page.corsight_person.trigger.start');
        case 1:
            return t('page.corsight_person.trigger.forced_update');
        case 2:
            return t('page.corsight_person.trigger.end');
        case 3:
            return t('page.corsight_person.trigger.periodic');
        default:
            return trigger === null ? '-' : String(trigger);
    }
};

// Only real watchlist hits are rendered — 'not_determined' is Fortify's normal
// "not on the list" result for the vast majority of visits
const matchedWatchlists = computed(() => (detail.form.watchlist_matches ?? []).filter((match) => match.outcome === 'matched'));

watch(
    () => props.person,
    (newData) => {
        if (newData?.id) {
            detail.processData(newData);
        }
    },
    { immediate: true },
);
</script>

<template>
    <ModalShowForm variant="medium" :open="show" @close="emit('close')" :title="$t('page.corsight_person.detail.title')" icon="lucide:scan-face">
        <div class="flex flex-col gap-6">
            <!-- Person overview -->
            <div class="flex flex-col gap-4 sm:flex-row sm:gap-5">
                <div class="w-32 shrink-0">
                    <img
                        v-if="detail.form.image"
                        :src="`data:image/jpeg;base64,${detail.form.image}`"
                        :alt="$t('table.header.photo')"
                        class="size-32 rounded-xl border object-cover"
                    />
                    <div v-else class="bg-secondary/30 text-muted-foreground flex size-32 items-center justify-center rounded-xl border">-</div>
                    <p v-if="detail.form.face_score" class="fluid-text-xxs-xs text-muted-foreground mt-2 text-center">
                        {{ $t('page.corsight_person.label.face_score') }}:
                        <span class="text-foreground font-semibold">{{ detail.form.face_score }}</span>
                    </p>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <Badge v-if="detail.form.status === 'in'" variant="success" class="fluid-text-xxs-xs">
                                <span class="size-1.5 rounded-full bg-green-600" />
                                {{ $t('page.corsight_person.status.in') }}
                            </Badge>
                            <Badge v-else-if="detail.form.status === 'exited'" variant="destructive" class="fluid-text-xxs-xs">
                                {{ $t('page.corsight_person.status.exited') }}
                            </Badge>
                            <Badge v-else variant="outline" class="fluid-text-xxs-xs">
                                {{ $t('page.corsight_person.status.abandoned') }}
                            </Badge>

                            <p class="fluid-text-sm-base mt-2 truncate font-bold">
                                {{ detail.form.role ? $t(`page.corsight_person.role.${detail.form.role}`) : '-' }}
                            </p>
                            <p class="fluid-text-xxs-xs text-muted-foreground mt-1 truncate">
                                {{ [detail.form.room_area?.name, detail.form.room?.name].filter(Boolean).join(' · ') || '-' }}
                            </p>
                        </div>

                        <div class="bg-secondary/30 shrink-0 rounded-lg border px-3 py-2">
                            <p class="fluid-text-xs-sm max-w-40 truncate font-semibold">{{ detail.form.camera || '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <div
                            class="rounded-lg border px-3 py-2.5"
                            :class="detail.form.security_flag ? 'border-destructive/60 bg-destructive/5' : 'bg-secondary/30'"
                        >
                            <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('table.header.enter_at') }}</p>
                            <p class="fluid-text-xxs-xs mt-1 truncate font-semibold">{{ detail.form.enter_at || '-' }}</p>
                        </div>
                        <div
                            class="rounded-lg border px-3 py-2.5"
                            :class="detail.form.security_flag ? 'border-destructive/60 bg-destructive/5' : 'bg-secondary/30'"
                        >
                            <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('table.header.last_seen_at') }}</p>
                            <p class="fluid-text-xxs-xs mt-1 truncate font-semibold">{{ detail.form.last_seen_at || '-' }}</p>
                        </div>
                        <div
                            class="rounded-lg border px-3 py-2.5"
                            :class="detail.form.security_flag ? 'border-destructive/60 bg-destructive/5' : 'bg-secondary/30'"
                        >
                            <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('table.header.dwell') }}</p>
                            <p class="fluid-text-xxs-xs mt-1 truncate font-semibold">{{ formatDwell(detail.form.dwell_seconds) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security alert (confirmed POI identity or watchlist hit — keyed on security_flag) -->
            <div v-if="detail.form.security_flag" class="rounded-xl border border-red-200 bg-linear-to-br from-red-50/60 to-red-50/20 p-4">
                <!-- Header -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="flex size-6.5 items-center justify-center rounded-md bg-red-100">
                            <Icon icon="lucide:shield-alert" class="size-3.5 text-red-500" />
                        </div>
                        <div>
                            <p class="fluid-text-xxs-xs font-semibold tracking-wider text-red-600 uppercase">
                                {{ $t('page.corsight_person.detail.security_alert') }}
                            </p>
                            <p class="fluid-text-xxs-xs text-neutral-400">
                                {{ $t('page.corsight_person.label.matched_person') }}
                            </p>
                        </div>
                    </div>
                    <Badge variant="destructive" class="fluid-text-xxs-xs rounded-full uppercase">
                        <span class="size-1.5 rounded-full bg-red-500" />
                        {{ $t('page.corsight_person.match_outcome.matched') }}
                    </Badge>
                </div>

                <!-- Match details (identity + watchlist, bottom-aligned) -->
                <div class="mt-3 flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-5">
                    <div v-if="detail.form.poi_name" class="fluid-text-xxs-xs min-w-0">
                        <p class="fluid-text-sm-base mt-0.5 truncate font-bold text-red-800">{{ detail.form.poi_name }}</p>
                        <p v-if="detail.form.poi_confidence" class="fluid-text-xxs-xs mt-1 text-neutral-500">
                            {{ $t('page.corsight_person.label.match_confidence') }}
                            <span class="font-semibold text-red-600">{{ detail.form.poi_confidence }}%</span>
                        </p>
                    </div>

                    <div v-if="matchedWatchlists.length" class="shrink-0">
                        <p class="fluid-text-xxs-xs mb-1 tracking-wider text-neutral-400 uppercase sm:text-right">
                            {{ $t('page.corsight_person.label.watchlists') }}
                        </p>
                        <div class="flex flex-wrap gap-1.5 sm:justify-end">
                            <Badge v-for="(match, index) in matchedWatchlists" :key="index" variant="destructive" class="fluid-text-xxs-xs">
                                {{ match.name }}
                                <template v-if="match.repeated_alert"> ({{ $t('page.corsight_person.label.repeated_alert') }})</template>
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visit details -->
            <div>
                <p class="fluid-text-xxs-xs text-muted-foreground mb-2 font-semibold tracking-wider uppercase">
                    {{ $t('page.corsight_person.detail.visit_details') }}
                </p>
                <div class="bg-border grid grid-cols-1 gap-px overflow-hidden rounded-xl border sm:grid-cols-2">
                    <div class="bg-background px-4 py-3">
                        <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('table.header.gender') }}</p>
                        <p class="fluid-text-xs-sm mt-1 truncate font-semibold">{{ detail.form.gender || '-' }}</p>
                    </div>
                    <div class="bg-background px-4 py-3">
                        <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('table.header.age_group') }}</p>
                        <p class="fluid-text-xs-sm mt-1 truncate font-semibold">{{ detail.form.age_group || '-' }}</p>
                    </div>
                    <div class="bg-background px-4 py-3">
                        <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('page.corsight_person.label.liveness') }}</p>
                        <p class="fluid-text-xs-sm mt-1 truncate font-semibold">{{ detail.form.liveness || '-' }}</p>
                    </div>
                    <div class="bg-background px-4 py-3">
                        <p class="fluid-text-xxs-xs text-muted-foreground">{{ $t('page.corsight_person.label.mask') }}</p>
                        <p class="fluid-text-xs-sm mt-1 truncate font-semibold">{{ detail.form.mask_outcome || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Event trail -->
            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <p class="fluid-text-xxs-xs text-muted-foreground font-semibold tracking-wider uppercase">
                        {{ $t('page.corsight_person.detail.trail_title') }}
                    </p>
                    <p v-if="detail.form.trail.length" class="fluid-text-xxs-xs text-muted-foreground">
                        {{ $t('page.corsight_person.detail.event_count', { count: detail.form.trail.length }) }}
                    </p>
                </div>
                <div class="overflow-hidden rounded-xl border">
                    <div class="bg-secondary/30 text-muted-foreground grid grid-cols-2 gap-3 border-b px-4 py-2">
                        <p class="fluid-text-xxs-xs font-semibold">{{ $t('table.header.received_at') }}</p>
                        <p class="fluid-text-xxs-xs font-semibold">{{ $t('table.header.event_type') }}</p>
                    </div>
                    <div v-if="detail.form.trail.length" class="max-h-60 overflow-y-auto">
                        <div
                            v-for="(item, index) in detail.form.trail"
                            :key="index"
                            class="grid grid-cols-2 items-center gap-3 border-b px-4 py-2.5 last:border-b-0"
                        >
                            <p class="fluid-text-xs-sm truncate">{{ item.received_at }}</p>
                            <p class="fluid-text-xs-sm flex min-w-0 items-center gap-1.5 font-medium">
                                <span class="size-1.5 shrink-0 rounded-full bg-indigo-500" />
                                <span class="truncate">{{ triggerLabel(item.trigger) }}</span>
                            </p>
                        </div>
                    </div>
                    <p v-else class="fluid-text-xxs-xs text-muted-foreground px-4 py-3">
                        {{ $t('page.corsight_person.detail.trail_empty') }}
                    </p>
                </div>
            </div>
        </div>
    </ModalShowForm>
</template>
