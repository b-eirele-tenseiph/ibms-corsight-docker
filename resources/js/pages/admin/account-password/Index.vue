<script setup lang="ts">
import { AccountInfoSubHeader } from '@/components/admin/account-info';
import { InputWithLabel } from '@/components/shared/inputs';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { AccountSettingsLayout } from '@/layouts/settings';
import { passwordUpdate } from '@/routes/admin/settings';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(passwordUpdate.url(), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <Head :title="$t('head.password_settings')" />
    <AdminLayout title="head.password_settings">
        <AccountSettingsLayout>
            <div class="space-y-6">
                <AccountInfoSubHeader
                    title="page.account_information.header.password"
                    description="page.account_information.header.password_subtitle"
                />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <InputWithLabel
                        ref="currentPasswordInput"
                        type="password"
                        v-model="form.current_password"
                        label="form.label.current_password"
                        placeholder="form.label.current_password"
                        autocomplete="current-password"
                        :error="form.errors.current_password"
                    />

                    <InputWithLabel
                        ref="passwordInput"
                        type="password"
                        v-model="form.password"
                        label="form.label.new_password"
                        placeholder="form.label.new_password"
                        autocomplete="new-password"
                        :error="form.errors.password"
                    />

                    <InputWithLabel
                        type="password"
                        v-model="form.password_confirmation"
                        label="form.label.confirm_password"
                        placeholder="form.label.confirm_password"
                        autocomplete="new-password"
                        :error="form.errors.password_confirmation"
                    />

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ $t('action.save') }}</Button>
                    </div>
                </form>
            </div>
        </AccountSettingsLayout>
    </AdminLayout>
</template>
