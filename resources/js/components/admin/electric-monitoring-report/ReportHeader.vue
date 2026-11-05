<script setup lang="ts">
import { ReportHeader } from '@/types';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);
dayjs.extend(timezone);

const props = defineProps<ReportHeader>();

const start = dayjs(props.filters?.start).format('MMMM DD YYYY');
const end = dayjs(props.filters?.end).format('MMMM DD YYYY');

const days = dayjs(props.filters?.end).diff(props.filters?.start, 'day') + 1;

const generatedDate = dayjs().tz('Asia/Manila').format('MMMM DD YYYY hh:mm A');
</script>
<template>
    <div class="flex flex-col gap-4">
        <div class="border-primary/20 bg-primary/10 mb-8 space-y-4 rounded-lg border p-6">
            <div class="grid grid-cols-2 justify-between gap-4 text-sm">
                <div class="space-y-1">
                    <p class="text-secondary-text font-medium">{{ $t('page.electric_monitoring_report.header.company_name') }}</p>
                    <p class="text-primary-text font-semibold">Tensei Philippines Inc.</p>
                </div>
                <div class="space-y-1">
                    <p class="text-secondary-text font-medium">{{ $t('page.electric_monitoring_report.header.reporting_period') }}</p>
                    <p class="text-primary-text font-semibold">
                        {{ start }} - {{ end }} ({{ days }} {{ $t('page.electric_monitoring_report.label.days') }})
                    </p>
                </div>
                <div class="space-y-1">
                    <p class="text-secondary-text font-medium">{{ $t('page.electric_monitoring_report.header.generated_date') }}</p>
                    <p class="text-primary-text font-semibold">{{ generatedDate }}</p>
                </div>
            </div>

            <div class="border-primary/20 mt-4 border-t pt-4">
                <div class="flex gap-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-secondary-text font-medium">{{ $t('page.electric_monitoring_report.label.selected_meter') }}</p>
                        <p class="text-primary-text font-medium">
                            <template v-for="(meter, index) in meters" :key="meter.id">
                                <span class="font-semibold">{{ meter.name }}</span>
                                <span v-if="index < meters.length - 1">, </span>
                            </template>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
