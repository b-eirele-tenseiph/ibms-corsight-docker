<script setup lang="ts">
import { Switch } from '@/components/ui/switch';
import { enable } from '@/routes/admin/management/accounts/admins';
import { Admin } from '@/types/t-admin';
import { useForm } from '@inertiajs/vue3';
import { inject } from 'vue';
import { useI18n } from 'vue-i18n';

const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

const { t } = useI18n();

const props = defineProps<{
    admin: Admin;
}>();

const form = useForm({
    enabled: !!props.admin.enabled,
});

function updateAdminStatus() {
    form.put(enable(String(props.admin.id)).url, {
        preserveScroll: true,
        onError: () => {
            // Put error on toast
            toast.error(t('error.admin_update_status_failed'), {
                classes: {
                    toast: 'border-l-3 border-l-error',
                    icon: 'text-error [&>svg]:size-4 xs:[&>svg]:size-5',
                },
            });
        },
    });
}
</script>

<template>
    <Switch v-model="form.enabled" @update:model-value="updateAdminStatus" :disabled="form.processing" />
</template>
