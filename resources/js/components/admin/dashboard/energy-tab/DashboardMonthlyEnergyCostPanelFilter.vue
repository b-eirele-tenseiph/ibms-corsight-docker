<script setup lang="ts">
import { DropdownContent } from '@/components/shared/dropdowns';
import { DropdownMenuContent, DropdownMenuLabel, DropdownMenuRadioGroup, DropdownMenuRadioItem } from '@/components/ui/dropdown-menu';
import { DateOption } from '@/types';

defineProps<{
    months: Pick<DateOption, 'label'>[];
    modelValue: string;
}>();

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <DropdownContent buttonName="operation.filter" icon="heroicons-outline:filter">
        <template #content>
            <DropdownMenuContent align="end" class="min-w-40">
                <!-- Months -->
                <DropdownMenuLabel class="text-xs font-bold">{{ $t('page.admin_dashboard.label.month') }}</DropdownMenuLabel>
                <DropdownMenuRadioGroup :model-value="modelValue" @update:model-value="(val) => emit('update:modelValue', val)">
                    <template v-for="month in months" :key="month.label">
                        <DropdownMenuRadioItem :value="month.label" class="fluid-text-xs-sm">
                            {{ $t(`date_range_filter.${month.label}`) }}
                        </DropdownMenuRadioItem>
                    </template>
                </DropdownMenuRadioGroup>
            </DropdownMenuContent>
        </template>
    </DropdownContent>
</template>
