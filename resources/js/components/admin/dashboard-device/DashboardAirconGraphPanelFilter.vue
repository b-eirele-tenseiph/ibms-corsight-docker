<script setup lang="ts">
import { DropdownContent } from '@/components/shared/dropdowns';
import { DropdownMenuContent, DropdownMenuLabel, DropdownMenuRadioGroup, DropdownMenuRadioItem } from '@/components/ui/dropdown-menu';
import { DateOption } from '@/types';

defineProps<{
    days: Array<DateOption>;
    modelValue: string;
}>();

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <DropdownContent buttonName="operation.filter" icon="heroicons-outline:filter">
        <template #content>
            <DropdownMenuContent align="end" class="min-w-[10rem]">
                <!-- Months -->
                <DropdownMenuLabel class="text-xs font-bold">{{ $t('page.admin_dashboard.label.day') }}</DropdownMenuLabel>
                <DropdownMenuRadioGroup :model-value="modelValue" @update:model-value="(val) => emit('update:modelValue', val)">
                    <template v-for="day in days" :key="day.value">
                        <DropdownMenuRadioItem :value="day.value" class="fluid-text-xs-sm">
                            {{ $t(`date_range_filter.${day.label}`) }}
                        </DropdownMenuRadioItem>
                    </template>
                </DropdownMenuRadioGroup>
            </DropdownMenuContent>
        </template>
    </DropdownContent>
</template>
