<script setup lang="ts">
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
const model = defineModel<boolean>('open');
</script>

<template>
    <Sheet v-model:open="model">
        <SheetTrigger class="flex">
            <slot name="trigger" />
        </SheetTrigger>

        <SheetContent data-mobile="true" :showClose="true" :class="cn(props.class, 'w-full sm:min-w-xl sm:rounded-l-lg')">
            <slot name="content" />
        </SheetContent>
    </Sheet>
</template>
