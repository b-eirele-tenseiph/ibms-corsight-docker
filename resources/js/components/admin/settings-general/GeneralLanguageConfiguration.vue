<script setup lang="ts">
import { SettingsInfoSubHeader } from '@/components/admin/settings';
import { SelectArea } from '@/components/shared/selects';
import { Label } from '@/components/ui/label';
import { useLocalization } from '@/composables/useLocalization';

const { currentLocale, setLocale } = useLocalization();

const languages = [
    { value: 'en', label: 'English', flag: 'circle-flags:us' },
    { value: 'ja', label: '日本語', flag: 'circle-flags:jp' },
];
</script>

<template>
    <div class="space-y-6">
        <SettingsInfoSubHeader title="page.general_settings.header.display" description="page.general_settings.header.display_subtitle" />

        <div class="grid w-56 gap-2">
            <Label class="fluid-text-xs-sm">{{ $t('form.label.language') }}</Label>
            <SelectArea
                :items="languages"
                v-model="currentLocale"
                @update:model-value="(value) => setLocale(value as string)"
                valueKey="value"
                itemKey="label"
            />
        </div>
    </div>
</template>
