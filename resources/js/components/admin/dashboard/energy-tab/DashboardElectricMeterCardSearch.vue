<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { ref } from 'vue';

const props = defineProps<{ modelValue: string }>();
const emit = defineEmits(['update:modelValue']);

const search = ref(props.modelValue);
let timeout: any = null;

const load = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => emit('update:modelValue', search.value), 500);
};
</script>

<template>
    <InputSearch v-model="search" @input="load" />
</template>
