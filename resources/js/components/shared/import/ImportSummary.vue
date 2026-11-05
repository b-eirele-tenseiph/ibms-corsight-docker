<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { ImportSummary } from '@/types';
import { computed, ref } from 'vue';

const props = defineProps<{ summary?: ImportSummary | null }>();

const open = ref(false);

const successPct = computed(() =>
    Math.max(
        0,
        Math.min(
            100,
            Number.isFinite(Number(props.summary?.processed)) && Number(props.summary?.processed) > 0
                ? Math.round((Number(props.summary?.successful ?? 0) / Number(props.summary?.processed)) * 100)
                : 0,
        ),
    ),
);

const copyErrors = () => (props.summary?.errors?.length ? navigator.clipboard.writeText(props.summary.errors.join('\n')) : void 0);
</script>

<template>
    <Card v-if="summary" class="border-muted/60">
        <!-- <CardHeader class="flex flex-row items-start justify-between space-y-0"> -->
        <CardHeader class="flex flex-col gap-2 space-y-0 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
            <div class="space-y-2">
                <CardTitle class="fluid-text-xs-sm">
                    {{ $t('modal.title.import_summary') }}
                </CardTitle>

                <!-- flex-col on mobile, flex-row with gaps on sm+ -->
                <CardDescription class="fluid-text-xs-sm xs:flex-row xs:flex-wrap xs:gap-2 mt-1 flex flex-col gap-0.5">
                    <span>{{ summary.processed }} {{ $t('status.processed') }} </span>
                    <span class="text-secondary-text xs:inline hidden"> •</span>
                    <span class="font-medium text-green-600"> {{ summary.successful }} {{ $t('status.successful') }} </span>
                    <span class="text-secondary-text xs:inline hidden"> •</span>
                    <span class="font-medium text-red-600"> {{ summary.failed }} {{ $t('status.unsuccessful') }} </span>
                </CardDescription>
            </div>

            <Badge :variant="summary.successful ? 'success' : 'secondary'" class="hidden rounded-full sm:block">{{
                summary.successful ? summary.successful + ' ' + $t('status.successful') : $t('modal.alert.no_successful_import')
            }}</Badge>
        </CardHeader>

        <CardContent class="space-y-4">
            <!-- Ratio -->
            <div>
                <div class="text-muted-foreground mb-1 flex items-center justify-between text-xs">
                    <span>{{ $t('modal.title.success_rate') }}</span
                    ><span>{{ successPct }}%</span>
                </div>
                <Progress :model-value="successPct" />
            </div>

            <Separator />

            <!-- Errors -->
            <div v-if="summary.errors?.length" class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="fluid-text-xs-sm font-medium">{{ $t('modal.title.failed_imports') }}</div>
                    <div class="flex items-center">
                        <Button type="button" size="sm" variant="link" @click.prevent="copyErrors" class="fluid-text-xs-sm">{{
                            $t('action.copy')
                        }}</Button>
                        <Button type="button" size="sm" variant="ghost" @click="open = !open" class="fluid-text-xs-sm">
                            {{ open ? $t('action.hide') : $t('action.show') }} ({{ summary.errors.length }})
                        </Button>
                    </div>
                </div>

                <transition name="fade">
                    <ul v-show="open" class="fluid-text-xs-sm max-h-22 list-disc space-y-1 overflow-y-auto pl-5">
                        <li v-for="(e, i) in summary.errors" :key="i">{{ e }}</li>
                    </ul>
                </transition>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
