<script setup lang="ts">
import { Icon } from '@iconify/vue';

defineProps<{
    isArchivedActive: boolean;
}>();
</script>

<template>
    <div v-if="isArchivedActive" class="border-b py-4">
        <div class="flex items-start">
            <Icon icon="lucide:triangle-alert" class="text-warning mt-1 mr-3 size-4 flex-shrink-0" />
            <div>
                <h3 class="text-warning fluid-text-sm-base mb-1 font-semibold">{{ $t('page.file_settings.header.auto_deletion') }}</h3>
                <p class="text-warning/85 fluid-text-xs-sm font-medium">{{ $t('page.file_settings.header.auto_deletion_description') }}</p>
            </div>
        </div>
    </div>
</template>
