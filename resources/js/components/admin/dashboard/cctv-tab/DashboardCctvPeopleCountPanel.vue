<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { Card } from '@/components/ui/card';
import { CorsightPeopleCount } from '@/types';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    data: CorsightPeopleCount;
    isLoading: boolean;
}>();

const { t } = useI18n();

const allCamerasOnline = computed(() => props.data.active_cameras === props.data.total_cameras);
const offlineCameras = computed(() => props.data.total_cameras - props.data.active_cameras);

const tiles = computed(() => [
    {
        key: 'occupancy',
        label: t('page.admin_dashboard.cctv.label.occupancy'),
        icon: 'mdi:person-multiple',
        chip: 'bg-primary/10 text-primary',
        value: props.data.occupancy,
        caption: t('page.admin_dashboard.cctv.label.occupancy_caption'),
        captionClass: 'text-secondary-text',
    },
    {
        key: 'entered',
        label: t('page.admin_dashboard.cctv.label.entered'),
        icon: 'mdi:login-variant',
        chip: 'bg-success/10 text-success',
        value: props.data.entered,
        caption: t('page.admin_dashboard.cctv.label.entries_caption'),
        captionClass: 'text-secondary-text',
    },
    {
        key: 'exited',
        label: t('page.admin_dashboard.cctv.label.exited'),
        icon: 'mdi:logout-variant',
        chip: 'bg-warning/10 text-warning',
        value: props.data.exited,
        caption: t('page.admin_dashboard.cctv.label.exits_caption'),
        captionClass: 'text-secondary-text',
    },
    {
        key: 'active_cameras',
        label: t('page.admin_dashboard.cctv.label.active_cameras'),
        icon: 'mdi:cctv',
        chip: 'bg-info/10 text-info',
        value: props.data.active_cameras,
        caption: allCamerasOnline.value
            ? t('page.admin_dashboard.cctv.label.all_cameras_online')
            : t('page.admin_dashboard.cctv.label.cameras_offline', { count: offlineCameras.value }),
        captionClass: allCamerasOnline.value ? 'text-success' : 'text-warning',
    },
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
        <Card v-for="entry in tiles" :key="entry.key" class="flex flex-col gap-1 p-3">
            <template v-if="isLoading">
                <div class="flex min-h-16 items-center justify-center">
                    <SpinnerIcon />
                </div>
            </template>
            <template v-else>
                <div class="flex flex-row items-start justify-between gap-1">
                    <span class="fluid-text-xs-sm text-secondary-text truncate font-semibold">{{ entry.label }}</span>
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg" :class="entry.chip">
                        <Icon :icon="entry.icon" class="size-4" />
                    </span>
                </div>
                <div class="text-primary-text text-2xl font-bold">{{ entry.value }}</div>
                <div class="flex flex-row items-center gap-1">
                    <span v-if="entry.key === 'active_cameras'" class="fluid-text-xs-sm text-secondary-text">
                        {{ $t('page.admin_dashboard.cctv.label.total_cameras', { total: data.total_cameras }) }}
                    </span>
                    <span class="fluid-text-xs-sm truncate font-medium" :class="entry.captionClass">{{ entry.caption }}</span>
                </div>
            </template>
        </Card>
    </div>
</template>
