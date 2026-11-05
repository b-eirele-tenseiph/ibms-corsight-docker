<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Icon } from '@iconify/vue';

defineProps({
    content: String,
});
</script>

<template>
    <Popover>
        <PopoverTrigger class="hover:text-primary absolute top-1/2 -right-5 -translate-y-1/2 cursor-pointer">
            <Icon icon="lucide:info" class="text-muted-foreground/60 hover:text-info size-4" />
        </PopoverTrigger>

        <PopoverContent side="bottom" :side-offset="8" class="w-72 overflow-hidden p-0 shadow-md">
            <div class="flex items-start gap-2 p-4">
                <div class="bg-info/10 flex size-6 shrink-0 items-center justify-center rounded-full">
                    <Icon icon="lucide:philippine-peso" class="text-info size-3.5" />
                </div>

                <div class="flex flex-col gap-1.5 text-xs">
                    <span class="leading-none font-bold tracking-tight">
                        {{ $t('tooltip.unattributed_cost') }}
                    </span>
                    <span class="leading-relaxed">
                        {{ content }}
                    </span>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
