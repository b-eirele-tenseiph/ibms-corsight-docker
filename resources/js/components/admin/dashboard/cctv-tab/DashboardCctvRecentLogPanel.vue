<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { CorsightRecentLog } from '@/types';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';
import { computed } from 'vue';

const props = defineProps<{
    data: CorsightRecentLog[];
    isLoading: boolean;
}>();

const { isExtraSmallMobile, isTablet, isMobile, isSmallMobile } = useBreakpoints();

const isSmallScreen = computed(() => isTablet.value || isMobile.value || isSmallMobile.value);

const tableData = computed(() => {
    // If data exists or it's a small screen (don't force a dummy)
    return props.data.length > 0 || isSmallScreen.value ? props.data : [{}];
});

const genderIcon: Record<CorsightRecentLog['gender'], string> = {
    male: 'mdi:human-male',
    female: 'mdi:human-female',
    unknown: 'mdi:human',
};

const formatTime = (timestamp: string) => {
    const date = dayjs(timestamp);
    return date.isSame(dayjs(), 'day') ? date.format('HH:mm:ss') : date.format('MMM D, HH:mm');
};

const isEntered = (log: CorsightRecentLog) => log.direction === 'entered';

const columns = computed(() => ['time', 'camera_location', 'photo', 'gender', 'age_group', 'status']);
</script>

<template>
    <Card class="h-full gap-2 py-4">
        <div class="flex w-full items-center justify-between gap-2 px-4">
            <div class="flex flex-col">
                <span class="fluid-text-sm-base text-primary-text font-bold">
                    {{ $t('page.admin_dashboard.cctv.header.recent_log') }}
                </span>
                <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{
                    $t('page.admin_dashboard.cctv.header.recent_log_subtitle')
                }}</span>
            </div>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <div v-if="data?.length > 0" class="h-full overflow-hidden px-2">
                <ScrollArea class="h-full">
                    <Table class="table-main table-fixed">
                        <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                            <template v-for="(log, index) in tableData" :key="log.id ?? index">
                                <TableRow class="bg-table-header-background table-header-row hover:bg-transparent">
                                    <TableHead v-for="column in columns" :key="column" class="text-secondary-text h-12 py-3">
                                        {{ $t(`page.admin_dashboard.cctv.label.${column}`) }}
                                    </TableHead>
                                </TableRow>
                            </template>
                        </TableHeader>
                        <TableBody class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                            <TableRow v-for="log in data" :key="log.id" class="table-body-row">
                                <TableCell class="h-12 py-3 break-words whitespace-normal">{{ formatTime(log.timestamp) }}</TableCell>
                                <TableCell class="h-12 py-3 break-words whitespace-normal">
                                    <div class="flex items-center gap-2 font-medium">
                                        <span class="bg-primary/10 text-primary flex size-7 shrink-0 items-center justify-center rounded-lg">
                                            <Icon icon="mdi:cctv" class="size-3.5" />
                                        </span>
                                        <span class="truncate">{{ log.room }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="h-12 py-3">
                                    <div class="bg-accent text-primary flex size-8 items-center justify-center overflow-hidden rounded-full">
                                        <img
                                            v-if="log.image"
                                            :src="log.image"
                                            :alt="$t('page.admin_dashboard.cctv.label.person')"
                                            class="size-full object-cover"
                                        />
                                        <Icon v-else :icon="genderIcon[log.gender]" class="size-4" />
                                    </div>
                                </TableCell>
                                <TableCell class="h-12 py-3 break-words whitespace-normal">
                                    {{ $t(`page.admin_dashboard.cctv.label.${log.gender}`) }}
                                </TableCell>
                                <TableCell class="h-12 py-3 break-words whitespace-normal">{{ log.age_range }}</TableCell>
                                <TableCell class="h-12 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="isEntered(log) ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'"
                                    >
                                        <span class="size-1.5 rounded-full" :class="isEntered(log) ? 'bg-success' : 'bg-warning'" />
                                        {{
                                            isEntered(log)
                                                ? $t('page.admin_dashboard.cctv.label.entered_badge')
                                                : $t('page.admin_dashboard.cctv.label.exited_badge')
                                        }}
                                    </span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </ScrollArea>
            </div>
            <div v-else class="py-10">
                <StateEmpty label="page.admin_dashboard.cctv.label.no_logs" />
            </div>
        </template>
    </Card>
</template>
