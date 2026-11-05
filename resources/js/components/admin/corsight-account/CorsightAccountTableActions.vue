<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { Button } from '@/components/ui/button';
import { QueryParams } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    query: QueryParams;
}>();

const filters = ref(props.query);
let timeout: any = null;

const search = computed({
    get: () => filters.value.search ?? '',
    set: (newValue: string) => {
        if (newValue) {
            filters.value.search = newValue;
        } else {
            filters.value.search = undefined;
        }
        filters.value.page = 1;
    },
});

const load = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => router.reload({ data: filters.value }), 500);
};
</script>
<template>
    <div class="flex flex-row justify-between gap-4">
        <div class="w-full max-w-60">
            <InputSearch v-model="search" @input="load" />
        </div>
        <div class="flex flex-row justify-end gap-2">
            <Button variant="default" @click="$emit('open')">
                <Icon icon="lucide:plus" class="size-4" />
                <span class="hidden md:flex">{{ $t('modal.action.add_account') }}</span></Button
            >
        </div>
    </div>
</template>
