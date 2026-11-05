<script setup lang="ts">
import { AccountInfoSubHeader } from '@/components/admin/account-info';
import { InputWithLabel } from '@/components/shared/inputs';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { AccountSettingsLayout } from '@/layouts/settings';
import { profileUpdate } from '@/routes/admin/settings';
import { SharedData, User } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    username: user.username,
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(profileUpdate.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('head.profile_settings')" />
    <AdminLayout title="head.profile_settings">
        <AccountSettingsLayout>
            <div class="flex flex-col space-y-6">
                <AccountInfoSubHeader
                    title="page.account_information.header.profile"
                    description="page.account_information.header.profile_subtitle"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <InputWithLabel v-model="form.username" label="form.label.username" placeholder="form.label.username" disabled />

                    <InputWithLabel v-model="form.name" required label="form.label.name" placeholder="form.label.name" :error="form.errors.name" />

                    <InputWithLabel
                        type="email"
                        v-model="form.email"
                        label="form.label.email"
                        placeholder="form.label.email"
                        :error="form.errors.email"
                        required
                    />

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ $t('action.save') }}</Button>
                    </div>
                </form>
            </div>
        </AccountSettingsLayout>
    </AdminLayout>
</template>
