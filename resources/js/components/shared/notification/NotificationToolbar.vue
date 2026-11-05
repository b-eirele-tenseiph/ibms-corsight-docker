<script setup lang="ts">
import { cn } from '@/lib/utils';
import { AppNotificationToolbar } from '@/types';
import { Icon } from '@iconify/vue';
import type { HTMLAttributes } from 'vue';

const props = defineProps<AppNotificationToolbar & { class?: HTMLAttributes['class'] }>();

const emit = defineEmits<{
    (e: 'toggle-all', checked: boolean): void;
    (e: 'mark-as-read'): void;
    (e: 'cancel'): void;
}>();
</script>

<template>
    <div :class="cn('flex items-center gap-1', props.class)">
        <!-- Checkbox Section -->
        <div class="flex grow items-center gap-2 py-1">
            <input
                type="checkbox"
                :checked="checkAllValue"
                :indeterminate="isIndeterminate"
                @change="(e) => emit('toggle-all', (e.target as HTMLInputElement).checked)"
                class="accent-primary-text h-4 w-4 rounded hover:cursor-pointer"
            />
            <label class="text-sm font-medium">{{ $t('filter.select_all') }}</label>
        </div>

        <button
            @click="emit('mark-as-read')"
            :disabled="!anySelected"
            class="bg-primary flex items-center gap-1 rounded px-2 py-1 text-xs text-white transition-all"
            :class="!anySelected ? 'cursor-not-allowed opacity-50' : 'hover:bg-primary/90 cursor-pointer'"
        >
            <Icon icon="lucide:check" class="size-3.5" />
            {{ $t('page.notification.mark_as_read') }}
        </button>

        <button
            @click="emit('cancel')"
            class="border-sidebar-border bg-card text-secondary-text hover:bg-accent rounded border px-2 py-1 text-xs transition-colors hover:cursor-pointer"
        >
            {{ $t('action.cancel') }}
        </button>
    </div>
</template>
