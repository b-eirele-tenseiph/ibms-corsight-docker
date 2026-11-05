<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

defineProps<{
    label: string;
    value: string | number | null;
    unit?: string;
    isLoading?: boolean;
    totalConsumption?: number;
}>();
</script>

<template>
    <Card class="flex w-full flex-col px-4 py-2 shadow-xs">
        <div v-if="isLoading" class="w-full space-y-4">
            <Skeleton class="h-4 w-auto bg-gray-400/10" />
            <Skeleton class="h-4 w-auto bg-gray-400/10" />
        </div>
        <div v-else class="flex w-full flex-col">
            <span class="fluid-text-xxs-xs text-primary-text mb-1 truncate font-medium">{{ $t(label) }}</span>

            <div class="flex flex-row items-baseline gap-2">
                <span class="fluid-text-base-xl primary-text truncate font-bold">
                    {{ Number(value || 0).toLocaleString() }}
                    <template v-if="unit">
                        <label class="text-secondary-text text-sm font-bold">
                            {{ $t(unit) }}
                        </label>
                    </template>
                </span>
            </div>
        </div>
    </Card>
</template>
