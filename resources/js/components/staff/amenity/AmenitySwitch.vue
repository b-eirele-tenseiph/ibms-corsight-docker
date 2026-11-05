<script setup lang="ts">
import Switch from '@/components/ui/switch/Switch.vue';
import { toggleVisibility } from '@/routes/staff/amenity_management';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    amenityId: string;
    checked: boolean;
}>();

const checked = ref(props.checked);

const processing = ref(false);

function submit(newVal: boolean) {
    if (processing.value) return;
    const prev = checked.value;

    processing.value = true;
    checked.value = newVal;

    router.patch(
        toggleVisibility({ amenity: props.amenityId }).url,
        { is_visible: newVal },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => (checked.value = prev),
            onFinish: () => (processing.value = false),
        },
    );
}
</script>

<template>
    <Switch :model-value="checked" :disabled="processing" @update:model-value="submit" />
</template>
