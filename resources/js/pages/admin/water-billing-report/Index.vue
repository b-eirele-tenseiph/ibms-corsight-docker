<script setup lang="ts">
import { ReportBillingSummary, ReportMonthlyBillingHistory } from '@/components/admin/water-billing-report';
import MeterConsumptionPanel from '@/components/admin/water-meter-monitoring/MeterConsumptionPanel.vue';
import { AdminLayout } from '@/layouts/admin';
import { Head } from '@inertiajs/vue3';
import data from './Data.json';

const billingSummary = [...data.summary];

const billing_dates = [
    { date: '2026-02-01' },
    { date: '2026-01-01' },
    { date: '2025-12-01' },
    { date: '2025-11-01' },
    { date: '2025-10-01' },
    { date: '2025-09-01' },
    { date: '2025-08-01' },
    { date: '2025-07-01' },
    { date: '2025-06-01' },
    { date: '2025-05-01' },
    { date: '2025-04-01' },
    { date: '2025-03-01' },
    { date: '2025-02-01' },
    { date: '2025-01-01' },
];
</script>
<template>
    <Head :title="$t('head.water_billing_report')" />
    <AdminLayout title="head.water_billing_report">
        <div class="bg-background grid grid-cols-1 gap-2 p-2 lg:grid-cols-[60%_40%]">
            <ReportBillingSummary :billing_dates="billing_dates" :billingSummary="billingSummary" />

            <div class="flex h-full flex-col gap-2">
                <MeterConsumptionPanel class="h-120" />
                <ReportMonthlyBillingHistory class="h-140" />
            </div>
        </div>
    </AdminLayout>
</template>
