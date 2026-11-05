<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { fileIndex } from '@/routes/admin/management/settings/system';
import { QueryParams } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileSearch } from '.';

const props = defineProps<{
    params: QueryParams;
    isArchivedActive: boolean;
}>();

const isArchivedActive = ref(props.isArchivedActive);

const toggleArchiveFilter = () => {
    isArchivedActive.value = !isArchivedActive.value;
    router.get(fileIndex({ query: { data: isArchivedActive.value ? 'archived' : 'all' } }).url, {}, { preserveState: true });
};
</script>

<template>
    <div class="flex flex-row gap-2">
        <FileSearch :query="params" />
        <Button :variant="isArchivedActive ? 'default' : 'secondary'" @click="toggleArchiveFilter">
            <Icon icon="lucide:archive-restore" class="size-3.5" />
            <span class="hidden md:flex">
                {{
                    $t('action.archive', {
                        state: isArchivedActive ? $t('action.viewing') : '',
                    })
                }}
            </span>
        </Button>
    </div>
</template>
