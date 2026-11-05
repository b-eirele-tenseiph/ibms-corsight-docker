<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import Separator from '@/components/ui/separator/Separator.vue';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import type { HTMLAttributes } from 'vue';
import { ref } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    header: string;
    title: string;
    description: string;
    failedAccounts: any[];
}>();

const isExpanded = ref(false);
</script>

<template>
    <div data-slot="dialog-header" :class="cn('fluid-text-xs-sm flex flex-col gap-2 text-center', props.class)">
        <DialogTitle>
            <Label class="fluid-text-sm-base font-bold lg:my-1">{{ $t(`modal.title.${props.header}`) }}</Label>
        </DialogTitle>
        <div class="mt-2">
            <Label class="fluid-text-xs-sm font-semibold">{{ $t(`modal.title.${props.title}`) }}</Label>
            <DialogDescription class="mt-1 text-start text-xs">
                {{ $t(`modal.description.${props.description}`) }}
            </DialogDescription>
        </div>

        <!-- failed accounts -->
        <div v-if="failedAccounts.length" class="border-error-border bg-error-bg rounded-md border">
            <Button
                variant="ghost"
                class="text-error-text hover:bg-error-bg hover:text-error-text flex w-full cursor-pointer items-center justify-between p-2 text-xs font-semibold"
                @click="isExpanded = !isExpanded"
            >
                <p class="flex gap-1">
                    <Icon icon="lucide:alert-circle" class="size-3.5" />
                    {{ $t('table.header.failed_accounts') }} ({{ failedAccounts.length }})
                </p>
                <Icon icon="lucide:chevron-down" class="size-3.5 transition-transform duration-300" :class="{ 'rotate-180': isExpanded }" />
            </Button>

            <div class="overflow-hidden transition-all duration-300 ease-in-out" :class="isExpanded ? 'max-h-40 opacity-100' : 'max-h-0 opacity-0'">
                <div class="px-2 pb-2">
                    <p class="text-error-text mb-6 text-left text-xs">
                        {{ $t('modal.hint.failed_accounts_note') }}
                    </p>

                    <div v-for="(account, index) in failedAccounts" :key="account.room_id">
                        <div class="flex items-center justify-between py-0.5 text-xs">
                            <span class="text-error-text">{{ account.room_name }}</span>
                            <span class="text-error-text rounded-full bg-red-200 px-2 py-0.5">
                                {{ account.reason }}
                            </span>
                        </div>
                        <Separator v-if="index < failedAccounts.length - 1" class="my-1 bg-red-300/50" />
                    </div>
                </div>
            </div>
        </div>

        <slot />
    </div>
</template>
