<script setup lang="ts">
import { DropdownContent } from '@/components/shared/dropdowns';
import { InputSelectWithLabel } from '@/components/shared/inputs';
import { DropdownMenuContent } from '@/components/ui/dropdown-menu';

defineProps<{
    selectedType: string;
    selectedMeter: string;
}>();

const emit = defineEmits<{
    'update:selectedType': [value: string];
    'update:selectedMeter': [value: string];
}>();

const consumerTypes = [
    { id: 1, name: 'All', value: 'all' },
    { id: 2, name: 'Residential', value: 'residential' },
    { id: 3, name: 'Commercial', value: 'commercial' },
];

const meterSize: Array<any> = [
    { id: 1, name: 'All', value: 'all' },
    { id: 2, name: '1/2', value: '1/2' },
    { id: 3, name: '3/4', value: '3/4' },
    { id: 4, name: '1', value: '1' },
];
</script>

<template>
    <DropdownContent buttonName="operation.filter" icon="heroicons-outline:filter">
        <template #content>
            <DropdownMenuContent align="end" class="min-w-60 p-2 px-4">
                <h3 class="fluid-text-xs-sm pb-2 font-medium">{{ $t('page.activity_log.label.filter') }}</h3>

                <InputSelectWithLabel
                    class="py-2"
                    :items="consumerTypes"
                    :model-value="selectedType"
                    value-key="value"
                    label="form.label.consumer_type"
                    placeholder="form.placeholder.consumer_type"
                    @update:model-value="emit('update:selectedType', $event)"
                />

                <InputSelectWithLabel
                    class="py-2"
                    :items="meterSize"
                    :model-value="selectedMeter"
                    value-key="value"
                    label="form.label.meter_size"
                    placeholder="form.placeholder.meter_size_select"
                    @update:model-value="emit('update:selectedMeter', $event)"
                />
            </DropdownMenuContent>
        </template>
    </DropdownContent>
</template>
