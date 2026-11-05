<script lang="ts" setup>
import { cn } from '@/lib/utils';
import { SpinnerIcon } from '../icons/spinner-icon';

defineProps<{
    title?: string;
    description?: string;
    loading?: boolean;
    class?: string;
}>();
</script>

<template>
    <div v-if="loading !== false" :class="cn('flex flex-col items-center justify-center gap-6 lg:h-72', $props.class)">
        <SpinnerIcon />
        <div class="xs:flex hidden flex-col items-center">
            <span class="fluid-text-sm-base font-semibold">
                {{ title || $t('state.loading.title') }}
            </span>
            <span class="fluid-text-xs-sm mt-2 text-gray-500">
                {{ description || $t('state.loading.description') }}
            </span>
        </div>
        <!-- Optional slot for even more customization -->
        <slot />
    </div>
</template>
