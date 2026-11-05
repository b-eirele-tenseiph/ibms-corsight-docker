<script setup lang="ts">
import { AccountInfoSubHeader } from '@/components/admin/account-info';
import { SelectArea } from '@/components/shared/selects';
import { Label } from '@/components/ui/label';
import { useLocalization } from '@/composables/useLocalization';
import { AdminLayout } from '@/layouts/admin';
import { AccountSettingsLayout } from '@/layouts/settings';
import { Head } from '@inertiajs/vue3';

const { currentLocale, setLocale } = useLocalization();

const languages = [
    { value: 'en', label: 'English', flag: 'circle-flags:us' },
    { value: 'ja', label: '日本語', flag: 'circle-flags:jp' },
];
</script>
<template>
    <Head :title="$t('head.display_settings')" />
    <AdminLayout title="head.display_settings">
        <AccountSettingsLayout>
            <div class="space-y-6">
                <AccountInfoSubHeader
                    title="page.account_information.header.display"
                    description="page.account_information.header.display_subtitle"
                />

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
        </AccountSettingsLayout>
    </AdminLayout>
</template>
