<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { formatReadableDate, formatRelativeTime } from '@/lib/utils';
import { AppNotificationItem } from '@/types';
import { Icon } from '@iconify/vue';

defineProps<AppNotificationItem>();

// Define Emits
const emit = defineEmits<{
    (e: 'click'): void;
    (e: 'update:popoverOpen', open: boolean): void;
    (e: 'selectForBulk'): void;
    (e: 'downloadClick', url: string): void;
}>();

const getIconConfig = (nature?: string) => {
    switch (nature) {
        case 'information':
            return { name: 'material-symbols:info-rounded', color: 'var(--info)' };
        case 'warning':
            return { name: 'material-symbols:warning-rounded', color: 'var(--warning)' };
        case 'error':
            return { name: 'material-symbols:add-circle-rounded', color: 'var(--error)' };
        case 'food':
            return { name: 'material-symbols:restaurant-rounded', color: 'var(--primary)' };
        default:
            return { name: '', color: '' };
    }
};
</script>

<template>
    <div
        @click="emit('click')"
        class="group hover:bg-controls-hovered flex w-full cursor-pointer items-center justify-between space-y-1 rounded-sm pr-4 transition"
        :class="{ 'bg-controls': !notif.read_at }"
    >
        <div class="items-top relative flex w-full flex-nowrap gap-2 rounded-md px-2 py-1.5">
            <!-- Checkbox -->
            <div v-if="isBulkSelecting" @click.stop>
                <input
                    type="checkbox"
                    v-model="notif.checked"
                    :disabled="!!notif.read_at"
                    class="accent-primary-text mt-0.5 h-4 w-4"
                    :class="notif.read_at ? 'opacity-50 hover:cursor-not-allowed' : 'hover:cursor-pointer'"
                />
            </div>

            <!-- Nature icon -->
            <Icon
                v-if="getIconConfig(notif.data.nature).name"
                :icon="getIconConfig(notif.data.nature).name"
                :style="{ color: getIconConfig(notif.data.nature).color }"
                :class="{ 'rotate-45': notif.data.nature === 'error' }"
                class="size-5 shrink-0 sm:size-6"
            />

            <!-- Content -->
            <div class="flex grow flex-col items-start gap-y-1">
                <div class="flex flex-col">
                    <h1 class="text-primary-text fluid-text-xs-sm" :class="!notif.read_at ? 'font-extrabold' : 'font-semibold'">
                        {{ notif.data.title }}
                    </h1>

                    <p class="text-primary-text fluid-text-xs-sm line-clamp-2" :class="!notif.read_at ? 'font-medium' : 'font-normal'">
                        {{ notif.data.message ?? notif.data.content }}
                    </p>
                </div>

                <Button
                    v-if="notif.data.metadata?.download_url"
                    @click.stop="emit('downloadClick', notif.data.metadata!.download_url)"
                    variant="link"
                    size="xs"
                    class="px-0!"
                >
                    <span class="fluid-text-xs-sm">
                        {{ $t('action.download', { file_type: $t('label.report') }) }}
                    </span>
                    <Icon icon="lucide:download" class="size-3.5" />
                </Button>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <p class="text-secondary-text fluid-text-xxs-xs" :class="!notif.read_at ? 'font-medium' : 'font-normal'">
                            {{ formatRelativeTime(notif.created_at) }}
                        </p>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>{{ formatReadableDate(notif.created_at) }}</p>
                    </TooltipContent>
                </Tooltip>
            </div>
        </div>

        <div class="flex gap-x-2">
            <!-- Row actions -->
            <div
                @click.stop
                class="m-0 transition-opacity duration-200"
                :class="isPopoverOpen ? 'opacity-100' : 'opacity-100 md:opacity-0 md:group-hover:opacity-100'"
            >
                <Popover :open="isPopoverOpen" @update:open="(val) => emit('update:popoverOpen', val)">
                    <PopoverTrigger
                        v-if="!notif.read_at && !notif.checked"
                        class="bg-controls-foreground border-border rounded-full border p-1 shadow-xs transition"
                    >
                        <Icon icon="lucide:more-horizontal" class="text-primary-text cursor-pointer" />
                    </PopoverTrigger>
                    <PopoverContent class="w-25 p-0.5">
                        <button
                            v-if="!notif.read_at && !notif.checked"
                            @click="emit('selectForBulk')"
                            class="hover:bg-accent/80 block w-full cursor-pointer rounded px-2 py-1 text-left text-xs"
                        >
                            {{ $t('page.notification.mark_as_read') }}
                        </button>
                    </PopoverContent>
                </Popover>
            </div>
        </div>
    </div>
</template>
