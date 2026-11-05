<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { animatedIconVariants } from './index';

defineProps<{
    containerClass?: string;
    pingClass?: string;
    iconClass?: string;
    icon: string;
}>();
</script>

<template>
    <div :class="cn(animatedIconVariants.container(), containerClass)">
        <span :class="cn(animatedIconVariants.ping(), pingClass)"></span>
        <div :class="cn(animatedIconVariants.icon(), iconClass)">
            <Icon :icon="icon" class="size-full" />
        </div>
    </div>
</template>
