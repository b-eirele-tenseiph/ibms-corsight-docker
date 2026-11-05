<script setup lang="ts">
import { DropdownContent } from '@/components/shared/dropdowns';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu';
import { DateOption, ElectricMeterData, MeterPhase } from '@/types';
import { watch } from 'vue';

const props = defineProps<{
    meterOptions: Array<ElectricMeterData>;
    phases: Array<MeterPhase>;
    hours: Array<DateOption>;
}>();

const filters = defineModel<{
    hour?: string;
    phase?: string;
    meters?: Array<ElectricMeterData>;
}>('filters', { default: () => ({ hour: '', phase: '', meters: [] }) });

// Toggle a meter in the filters.meters array
function toggleMeter(meter: any) {
    const id = meter?.id ?? meter;
    const meters = filters.value.meters ?? [];

    if (meters.length === 1 && (meters[0]?.id ?? meters[0]) === id) return;

    filters.value.meters = meters.some((m) => (m?.id ?? m) === id) ? meters.filter((m) => (m?.id ?? m) !== id) : [...meters, meter];
}

watch(
    () => props.meterOptions,
    (options) => {
        if (!options?.length) return;
        const valid = filters.value.meters?.filter((meter) => options.some((opt) => opt.id === meter?.id));
        filters.value.meters = valid?.length ? valid : options;
    },
    { immediate: true },
);
</script>

<template>
    <DropdownContent buttonName="operation.filter" icon="heroicons-outline:filter">
        <template #content>
            <DropdownMenuContent align="end" class="min-w-48">
                <DropdownMenuLabel class="text-xs font-bold">
                    {{ $t('page.admin_dashboard.label.meter') }}
                </DropdownMenuLabel>

                <DropdownMenuSub>
                    <DropdownMenuSubTrigger>
                        <span class="fluid-text-xs-sm cursor-pointer">
                            ({{ filters.meters?.length }}) {{ $t('page.admin_dashboard.label.selected_meter') }}
                        </span>
                    </DropdownMenuSubTrigger>
                    <DropdownMenuPortal>
                        <DropdownMenuSubContent class="w-fit py-2">
                            <template v-for="m in meterOptions" :key="m.id">
                                <div
                                    class="fluid-text-xs-sm hover:bg-accent flex cursor-pointer items-center gap-2 rounded-sm p-2"
                                    @click="toggleMeter(m)"
                                >
                                    <Checkbox :model-value="filters.meters?.some((x) => x?.id === m?.id)" class="border-secondary-muted-text" />
                                    <span>{{ m.name }}</span>
                                </div>
                            </template>
                        </DropdownMenuSubContent>
                    </DropdownMenuPortal>
                </DropdownMenuSub>

                <DropdownMenuSeparator class="mx-2" />

                <DropdownMenuLabel class="mt-2 text-xs font-bold">
                    {{ $t('page.admin_dashboard.label.phase') }}
                </DropdownMenuLabel>
                <DropdownMenuRadioGroup v-model:model-value="filters.phase">
                    <template v-for="p in phases" :key="p.label">
                        <DropdownMenuRadioItem :value="p.label" class="fluid-text-xs-sm cursor-pointer">
                            {{ $t(`filter.${p.label}`) }}
                        </DropdownMenuRadioItem>
                    </template>
                </DropdownMenuRadioGroup>

                <DropdownMenuSeparator class="mx-2" />

                <DropdownMenuLabel class="text-xs font-bold">
                    {{ $t('page.admin_dashboard.label.hour') }}
                </DropdownMenuLabel>
                <DropdownMenuRadioGroup v-model:model-value="filters.hour">
                    <template v-for="h in hours" :key="h.label">
                        <DropdownMenuRadioItem :value="h.value" class="fluid-text-xs-sm cursor-pointer">
                            {{ $t(`date_range_filter.${h.label}`) }}
                        </DropdownMenuRadioItem>
                    </template>
                </DropdownMenuRadioGroup>
            </DropdownMenuContent>
        </template>
    </DropdownContent>
</template>
