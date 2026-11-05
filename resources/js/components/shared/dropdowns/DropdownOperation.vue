<script lang="ts" setup>
import { DropdownContent } from '@/components/shared/dropdowns';
import { DropdownMenuContent, DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';

const props = defineProps<{
    operations?: Array<'template' | 'import' | 'export'>;
}>();

defineEmits<{
    (e: 'template' | 'import' | 'export'): void;
}>();

const config = {
    template: { icon: 'lets-icons:download', label: 'operation.template' },
    import: { icon: 'lets-icons:import', label: 'operation.import' },
    export: { icon: 'lets-icons:export', label: 'operation.export' },
} as const;
</script>

<template>
    <DropdownContent buttonName="operation.action" align="end">
        <template #content>
            <DropdownMenuContent align="end" class="min-w-[10rem]">
                <DropdownMenuItem
                    v-for="operation in props.operations"
                    :key="operation"
                    @click="$emit(operation)"
                    class="fluid-text-xs-sm flex cursor-pointer items-center gap-2"
                >
                    <Icon :icon="config[operation].icon" class="text-secondary-text size-4" />
                    <span>{{ $t(config[operation].label) }}</span>
                </DropdownMenuItem>

                <slot name="extension" />
            </DropdownMenuContent>
        </template>
    </DropdownContent>
</template>
