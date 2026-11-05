<template>
    <div class="border-primary/20 bg-primary/10 mb-8 space-y-4 rounded-lg border p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ $t('page.electric_monitoring_report.header.disclaimers') }}</h3>
        <ul class="list-inside list-disc space-y-2 text-sm">
            <li>{{ $t('page.electric_monitoring_report.note.footer_data_collected') }}</li>
            <li>{{ $t('page.electric_monitoring_report.note.footer_carbon_emission', { carbon: 0.7181 }) }}</li>
            <li>{{ $t('page.electric_monitoring_report.note.footer_cost_estimates') }}</li>
        </ul>
    </div>
</template>
