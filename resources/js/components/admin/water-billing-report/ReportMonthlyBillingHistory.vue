<script setup lang="ts">
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ReportMonthlyBillingHistoryContent } from '.';

const tabs = [
    {
        value: 'chart',
        name: 'overview',
    },
    {
        value: 'table',
        name: 'monthly_data',
    },
];
</script>

<template>
    <Card class="h-full gap-4 py-4">
        <CardHeader>
            <div class="flex w-full flex-col">
                <span class="fluid-text-sm-base text-primary-text font-bold">
                    {{ $t('page.water_billing_report.header.billing_history') }}
                </span>
                <span class="fluid-text-xs-sm text-secondary-text font-semibold">
                    {{ $t('page.water_billing_report.header.billing_history_subtitle') }}
                </span>
            </div>
        </CardHeader>
        <CardContent class="h-full">
            <Tabs defaultValue="chart" class="h-full">
                <TabsList class="grid w-full grid-cols-2">
                    <TabsTrigger
                        v-for="tab in tabs"
                        :key="tab.value"
                        :value="tab.value"
                        class="data-[state=active]:bg-accent/60 fluid-text-xs-sm flex flex-col items-center justify-center gap-1 leading-tight capitalize data-[state=active]:shadow-none sm:flex-row sm:gap-2"
                    >
                        {{ $t(`page.water_billing_report.label.${tab.name}`) }}
                    </TabsTrigger>
                </TabsList>

                <ReportMonthlyBillingHistoryContent />
            </Tabs>
        </CardContent>
    </Card>
</template>
