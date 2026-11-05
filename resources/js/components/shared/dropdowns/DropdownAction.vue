<script lang="ts" setup>
import { DropdownContent } from '@/components/shared/dropdowns';
import { DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel } from '@/components/ui/dropdown-menu';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { Icon } from '@iconify/vue';

type actionType = 'update' | 'delete' | 'configure' | 'download' | 'restore' | 'forceDelete';

const props = withDefaults(
    defineProps<{
        actions?: Partial<Record<actionType, { label: string; class?: string }>>;
        showLabel?: boolean;
    }>(),
    { showLabel: true },
);

const icons = {
    update: 'lucide:edit-2',
    delete: 'lucide:trash-2',
    configure: 'solar:settings-linear',
    download: 'lucide:download',
    restore: 'ic:round-restore',
    forceDelete: 'material-symbols:scan-delete-outline-rounded',
} as const;
const { isExtraSmallMobile } = useBreakpoints();
</script>

<template>
    <DropdownContent buttonVariant="ghost" icon="lucide:ellipsis">
        <template #content>
            <DropdownMenuContent :align="isExtraSmallMobile ? 'start' : 'center'" class="min-w-[10rem]">
                <DropdownMenuLabel v-if="showLabel" class="pl-1 text-xs font-semibold">
                    {{ $t('operation.action') }}
                </DropdownMenuLabel>

                <DropdownMenuItem
                    v-for="(action, key) in props.actions"
                    :key="key"
                    @click="$emit(key)"
                    :class="['fluid-text-xs-sm cursor-pointer', action?.class]"
                >
                    <Icon :icon="icons[key]" :class="['size-3.5', action?.class || 'text-secondary-text']" />
                    {{ $t(action?.label) }}
                </DropdownMenuItem>

                <slot name="extension" />
            </DropdownMenuContent>
        </template>
    </DropdownContent>
</template>
