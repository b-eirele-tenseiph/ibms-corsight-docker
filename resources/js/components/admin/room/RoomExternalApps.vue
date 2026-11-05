<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { reactive, watch } from 'vue';

// Props: list of all available external applications
const props = defineProps<{
    apps: string[];
}>();

// Emits modelValue updates
const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

// Two-way binding for selected external apps
const modelValue = defineModel<string[]>();

// Internal reactive state to track each app's checkbox state
const appStates = reactive<Record<string, boolean>>({});

// Sync internal checkbox state when modelValue changes
watch(
    () => modelValue.value,
    (selected) => {
        const selectedArray = selected ?? [];
        props.apps.forEach((app) => {
            appStates[app] = selectedArray.includes(app);
        });
    },
    { immediate: true },
);

// Watch for checkbox changes and emit updated list of selected apps
watch(
    () => ({ ...appStates }),
    (states) => {
        const selected = Object.entries(states)
            .filter(([, checked]) => checked)
            .map(([app]) => app);

        emit('update:modelValue', selected);
    },
    { deep: true },
);
</script>

<template>
    <div class="grid w-full items-start space-y-3">
        <Label for="external-apps" class="fluid-text-xs-sm">
            {{ $t('form.label.external_apps') }}
        </Label>

        <div id="external-apps" class="bg-accent flex h-auto flex-wrap gap-6 rounded py-2 pl-2">
            <div v-for="app in apps" :key="app" class="flex items-center gap-2">
                <Checkbox
                    v-model="appStates[app]"
                    class="bg-background hover:border-primary hover:bg-muted shrink-0 overflow-hidden transition-colors"
                />
                <Label class="text-primary-text fluid-text-xxs-sm font-bold">{{ app }}</Label>
            </div>
        </div>
    </div>
</template>
