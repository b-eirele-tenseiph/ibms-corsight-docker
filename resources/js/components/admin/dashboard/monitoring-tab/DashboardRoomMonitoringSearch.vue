<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { useRooms } from '@/composables/useRooms';
import { computed } from 'vue';

const { searchTerm, loadRooms } = useRooms();

let timeout: any = null;

const search = computed({
    get: () => searchTerm.value,
    set: (val: string) => {
        searchTerm.value = val;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            loadRooms();
        }, 500);
    },
});
</script>

<template>
    <InputSearch v-model="search" />
</template>
