<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { computed, HTMLAttributes } from 'vue';

const props = withDefaults(
    defineProps<{
        open?: boolean;
        count?: number;
        triggerLabel?: string;
        title?: string;
        description?: string;
        applyLabel?: string;
        resetLabel?: string;
        class?: HTMLAttributes['class'];
        bodyClass?: HTMLAttributes['class'];
        hasAddButton?: boolean;
    }>(),
    {
        open: false,
        count: 0,
        triggerLabel: 'operation.filter',
        title: 'filter.add_filter',
        description: 'filter.add_filter_description',
        applyLabel: 'action.apply',
        resetLabel: 'action.reset',
        hasAddButton: true,
    },
);

const emit = defineEmits<{
    (e: 'update:open', v: boolean): void;
    (e: 'apply'): void;
    (e: 'reset'): void;
    (e: 'add'): void;
}>();

const modelOpen = computed({
    get: () => props.open,
    set: (v) => emit('update:open', v),
});

const onApply = () => {
    emit('apply');
    modelOpen.value = false;
};

const onReset = () => {
    emit('reset');
    modelOpen.value = false;
};
</script>

<template>
    <Popover v-model:open="modelOpen">
        <!-- Trigger -->
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('flex w-full max-w-50 justify-between', props.class)">
                <span class="flex w-full items-center justify-between gap-2">
                    <span class="fluid-text-xs-sm">{{ $t(triggerLabel) }}</span>
                    <span v-if="count > 0" class="bg-primary text-xxs grid size-4.5 place-items-center rounded-full font-semibold text-white">
                        {{ count }}
                    </span>
                </span>
                <Icon icon="tabler:adjustments-horizontal" class="text-secondary-muted-text size-4" />
            </Button>
        </PopoverTrigger>

        <!-- Content -->
        <PopoverContent align="end" side="bottom" :side-offset="8" class="max-w-70 p-0">
            <!-- Header -->
            <div class="flex flex-col gap-1 border-b px-4 py-3">
                <div class="flex items-center justify-between">
                    <h3 class="fluid-text-xs-sm font-semibold">{{ $t(title) }}</h3>
                    <Button v-if="hasAddButton" size="icon" class="size-6 rounded-md" @click="$emit('add')">
                        <Icon icon="mdi:plus" class="size-3.5" />
                    </Button>
                </div>
                <p class="text-secondary-text fluid-text-xs-sm font-medium">{{ $t(description) }}</p>
            </div>

            <!-- Body -->
            <div :class="cn('max-h-52 space-y-2 overflow-y-auto px-4 py-2', props.bodyClass)">
                <slot />
            </div>

            <!-- Footer -->
            <div class="flex gap-2 border-t px-4 py-2">
                <Button class="fluid-text-xs-sm flex-1" @click="onApply">
                    {{ $t(applyLabel) }}
                </Button>
                <Button variant="outline" class="fluid-text-xs-sm flex-1" @click="onReset">
                    {{ $t(resetLabel) }}
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
