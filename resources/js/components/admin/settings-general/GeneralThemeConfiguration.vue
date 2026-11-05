<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { useAppearance } from '@/composables/useAppearance';
import { Appearance } from '@/types';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import { SettingsInfoSubHeader } from '../settings';

const { appearance, updateAppearance } = useAppearance();

const themes = ref([
    {
        value: 'light',
        icon: 'line-md:moon-to-sunny-outline-loop-transition',
        bg: 'bg-white',
        header: 'bg-slate-100 border-slate-200',
        skeleton: 'bg-slate-200',
    },
    {
        value: 'dark',
        icon: 'line-md:sunny-outline-to-moon-loop-transition',
        bg: 'bg-slate-950',
        header: 'bg-slate-900 border-slate-800',
        skeleton: 'bg-slate-800',
    },
]);
</script>

<template>
    <div class="space-y-6">
        <SettingsInfoSubHeader title="page.general_settings.header.theme" description="page.general_settings.header.theme_subtitle" />

        <div class="space-y-3">
            <Label class="fluid-text-xs-sm">{{ $t('form.label.appearance') }}</Label>
            <div class="grid max-w-md grid-cols-2 gap-3">
                <button
                    v-for="theme in themes"
                    :key="theme.value"
                    @click="updateAppearance(theme.value as Appearance)"
                    :class="[
                        'relative flex cursor-pointer flex-col items-center gap-3 rounded-lg border p-4 transition-all',
                        appearance === theme.value ? 'border-primary bg-primary/10' : 'border-border bg-card',
                    ]"
                >
                    <div :class="['aspect-video w-full overflow-hidden rounded border', theme.bg]">
                        <div :class="['h-2 border-b', theme.header]"></div>
                        <div class="space-y-1.5 p-2">
                            <div :class="['h-1.5 w-3/4 rounded', theme.skeleton]"></div>
                            <div :class="['h-1 w-1/2 rounded', theme.skeleton, 'opacity-80']"></div>
                            <div :class="['h-1 w-2/3 rounded', theme.skeleton, 'opacity-60']"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Icon :icon="theme.icon" />
                        <span class="text-sm font-medium capitalize">{{ $t(`layout.appearance.${theme.value}`) }}</span>
                    </div>
                </button>
            </div>
            <p class="text-secondary-text mt-2 text-xs">{{ $t('page.general_settings.label.theme_description') }}</p>
        </div>
    </div>
</template>
