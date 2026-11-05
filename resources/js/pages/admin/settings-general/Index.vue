<script setup lang="ts">
import { AdminLayout } from '@/layouts/admin';
import { GeneralSettingsLayout, SettingsLayout } from '@/layouts/settings';
import GeneralLanguageConfiguration from '../../../components/admin/settings-general/GeneralLanguageConfiguration.vue';
import GeneralThemeConfiguration from '../../../components/admin/settings-general/GeneralThemeConfiguration.vue';
</script>

<template>
    <AdminLayout title="head.general_settings">
        <SettingsLayout>
            <GeneralSettingsLayout>
                <div class="max-w-4xl space-y-8">
                    <!-- Display Settings Section -->
                    <GeneralLanguageConfiguration />

                    <!-- Theme Configuration Section -->
                    <GeneralThemeConfiguration />
                </div>
            </GeneralSettingsLayout>
        </SettingsLayout>
    </AdminLayout>
</template>
