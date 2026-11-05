<script setup lang="ts">
import InputSearch from '@/components/shared/inputs/InputSearch.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Tabs from '@/components/ui/tabs/Tabs.vue';
import TabsList from '@/components/ui/tabs/TabsList.vue';
import TabsTrigger from '@/components/ui/tabs/TabsTrigger.vue';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { Icon } from '@iconify/vue';

defineProps<{
    modelValue: string;
    activeFilter: string;
}>();

const emit = defineEmits(['update:modelValue', 'filter-change']);

const { isLaptop, isDesktop, isTablet } = useBreakpoints();

// Added "All" to match your image reference (mapped to Processing)
const filterOptions = [
    { value: 'Processing', label: 'Processing' },
    { value: 'New', label: 'New Order' },
    { value: 'On cook', label: 'On Cook' },
    { value: 'Completed', label: 'Completed' },
    { value: 'Cancelled', label: 'Cancelled' },
];

const handleFilterClick = (value: string) => {
    emit('filter-change', value);
};
</script>

<template>
    <div class="mb-8 flex w-full flex-row items-center justify-between gap-4">
        <div class="w-full max-w-75">
            <InputSearch :model-value="modelValue" @update:model-value="(val) => emit('update:modelValue', val)" class="rounded-lg" />
        </div>

        <Tabs v-if="isTablet || isLaptop || isDesktop" :default-value="activeFilter" className="w-auto">
            <TabsList class="h-10 p-1">
                <TabsTrigger
                    v-for="opt in filterOptions"
                    :key="opt.value"
                    class="data-[state=active]:bg-primary data-[state=active]:text-white"
                    @click="handleFilterClick(opt.value)"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </TabsTrigger>
            </TabsList>
        </Tabs>

        <DropdownMenu v-else>
            <DropdownMenuTrigger as-child>
                <Button variant="outline" class="fluid-text-xs-sm flex min-w-25 items-center justify-between gap-4 px-4 font-normal">
                    <span>
                        {{ filterOptions.find((o) => o.value === activeFilter)?.label || 'All' }}
                    </span>
                    <Icon icon="lucide:chevrons-up-down" class="size-4 opacity-50" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-45 rounded-lg">
                <DropdownMenuItem
                    v-for="opt in filterOptions"
                    :key="opt.value"
                    @click="handleFilterClick(opt.value)"
                    class="fluid-text-xs-sm cursor-pointer px-4 py-2.5"
                    :class="{ 'bg-primary/5 text-primary font-semibold': activeFilter === opt.value }"
                >
                    {{ opt.label }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
