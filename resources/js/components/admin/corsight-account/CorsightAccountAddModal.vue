<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { store } from '@/routes/admin/management/corsight/accounts';
import { CorsightAccountCreateFormData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const addCorsight = reactive({
    form: useForm<CorsightAccountCreateFormData>({
        username: '',
        password: '',
        endpoint_secret: '',
        endpoint_url: '',
    }),
    onSubmit: () => {
        addCorsight.form.post(store.url(), {
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;

watch(
    () => props.show,
    (data) => {
        if (!data) {
            addCorsight.form.reset();
            addCorsight.form.clearErrors();
        }
    },
);
</script>

<template>
    <ModalForm
        :open="show"
        :isBlocked="!addCorsight.form.isDirty"
        @close="emit('close')"
        @submit="addCorsight.onSubmit"
        title="modal.action.add_corsight_account"
        confirmLabel="action.create"
        icon="lucide:scan-face"
    >
        <div class="flex flex-col gap-5">
            <InputWithLabel
                v-model="addCorsight.form.username"
                type="text"
                label="form.label.username"
                placeholder="form.placeholder.username"
                :error="addCorsight.form.errors.username"
            />
            <InputWithLabel
                v-model="addCorsight.form.password"
                type="password"
                label="form.label.password"
                placeholder="form.placeholder.password"
                :error="addCorsight.form.errors.password"
            />
            <InputWithLabel
                v-model="addCorsight.form.endpoint_secret"
                type="password"
                label="form.label.endpoint_secret"
                placeholder="form.placeholder.endpoint_secret"
                :error="addCorsight.form.errors.endpoint_secret"
            />
            <InputWithLabel
                v-model="addCorsight.form.endpoint_url"
                type="text"
                label="form.label.endpoint_url"
                placeholder="form.placeholder.endpoint_url"
                :error="addCorsight.form.errors.endpoint_url"
            />
        </div>
    </ModalForm>
</template>
