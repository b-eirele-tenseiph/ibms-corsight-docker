<script setup lang="ts">
import 'gridstack/dist/gridstack-all.js';
import 'gridstack/dist/gridstack.min.css';
import MeterConsumptionPanel from './MeterConsumptionPanel.vue';
import MeterGridItemLayout from './MeterGridItemLayout.vue';
import MeterListPanel from './MeterListPanel.vue';
import MeterRecentActivityPanel from './MeterRecentActivityPanel.vue';
import MeterSummaryCardPanel from './MeterSummaryCardPanel.vue';
import MeterTopConsumersPanel from './MeterTopConsumersPanel.vue';
</script>

<template>
    <div class="bg-background p-1">
        <div ref="grid" class="grid-stack">
            <MeterGridItemLayout
                :position="{
                    x: 0,
                    y: 0,
                    w: 6,
                    h: 1,
                }"
            >
                <MeterSummaryCardPanel />
            </MeterGridItemLayout>

            <MeterGridItemLayout
                :position="{
                    x: 0,
                    y: 1,
                    w: 6,
                    h: 3,
                }"
            >
                <MeterConsumptionPanel />
            </MeterGridItemLayout>

            <MeterGridItemLayout
                :position="{
                    x: 8,
                    y: 0,
                    w: 2,
                    h: 4,
                }"
            >
                <MeterTopConsumersPanel />
            </MeterGridItemLayout>

            <MeterGridItemLayout
                :position="{
                    x: 0,
                    y: 2,
                    w: 6,
                    h: 5,
                }"
            >
                <MeterListPanel />
            </MeterGridItemLayout>

            <MeterGridItemLayout
                :position="{
                    x: 8,
                    y: 2,
                    w: 2,
                    h: 5,
                }"
            >
                <MeterRecentActivityPanel />
            </MeterGridItemLayout>
        </div>
    </div>
</template>
