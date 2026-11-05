<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { Button } from '@/components/ui/button';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { login } from '@/routes/admin';
import { resetUpdate } from '@/routes/password';
import { Icon } from '@iconify/vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    token: string;
    success?: boolean;
}>();

// 1. Use form directly (not wrapped in reactive)
const form = useForm({
    token: props.token,
    password: '',
    password_confirmation: '',
});

const handleLoginRedirect = () => {
    router.get(login.url());
};

const onSubmit = () => {
    form.post(resetUpdate.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
            form.reset();
            setTimeout(() => {
                handleLoginRedirect();
            }, 5000);
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('head.reset_password')" />

        <div class="flex flex-col gap-y-4">
            <div v-if="props.success" class="flex flex-row items-center rounded-lg bg-green-50 p-4 text-sm text-green-800" role="alert">
                <div class="flex items-center justify-center">
                    <Icon icon="lucide:info" class="me-3 inline size-5 shrink-0" />
                </div>

                <div class="flex flex-col">
                    <div>
                        <span class="font-medium">{{ $t('page.admin_management.action.success_label') }}</span>
                    </div>
                    <div class="flex flex-row gap-1">
                        <div>{{ $t('page.admin_management.action.login_redirection') }}</div>
                        <span @click.prevent="handleLoginRedirect" class="cursor-pointer font-bold text-blue-700 underline">
                            {{ $t('action.login') }}
                        </span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="onSubmit" class="flex flex-col gap-y-6">
                <div class="flex flex-col gap-y-2">
                    <InputWithLabel
                        :label="$t('form.label.new_password')"
                        type="password"
                        :placeholder="$t('form.placeholder.new_password')"
                        v-model="form.password"
                        :error="form.errors.password"
                        required
                    />

                    <InputWithLabel
                        :label="$t('form.label.confirm_password')"
                        type="password"
                        :placeholder="$t('form.placeholder.confirm_password')"
                        v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation"
                        required
                    />
                </div>

                <div class="w-full">
                    <Button type="submit" :disabled="form.processing" class="w-full">
                        <Icon v-if="form.processing" icon="lucide:loader-2" class="mr-2 h-4 w-4 animate-spin" />
                        {{ $t('action.change_password') }}
                    </Button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
