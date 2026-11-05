<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Icon } from '@iconify/vue';
import { Time } from '@internationalized/date';
import { useVModel } from '@vueuse/core';
import { computed, ref } from 'vue';
import { TimePicker } from '../time-picker';

const props = defineProps<{
    label?: string;
    modelValue: string;
}>();

const emit = defineEmits(['update:modelValue']);
const model = useVModel(props, 'modelValue', emit);

const isOpen = ref(false);

const selectedTime = computed<Time | undefined>({
    get() {
        if (!model.value) return undefined;
        const [hour, minute] = model.value.split(':').map(Number);
        return new Time(hour, minute);
    },
    set(val) {
        if (!val) {
            model.value = '';
            return;
        }
        const hour = val.hour.toString().padStart(2, '0');
        const minute = val.minute.toString().padStart(2, '0');
        model.value = `${hour}:${minute}`;
        // Do NOT close the popover here
    },
});

// Format time as 12-hour with AM/PM
const formattedTime = computed(() => {
    if (!model.value) return 'No time selected';
    const [hour, minute] = model.value.split(':').map(Number);
    const period = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 === 0 ? 12 : hour % 12;
    return `${hour12}:${minute.toString().padStart(2, '0')} ${period}`;
});
</script>

<template>
    <Label v-if="label" class="fluid-text-xs-sm mb-2">{{ $t(label) }}</Label>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button variant="outline" class="h-full w-full justify-start text-left font-normal">
                <Icon icon="lucide:clock" class="mr-2 h-4 w-4" />
                <p>{{ formattedTime }}</p>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <TimePicker v-model="selectedTime" />
        </PopoverContent>
    </Popover>
</template>
