<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { credentials, update } from '@/routes/admin/management/corsight/accounts';
import { CorsightAccountData, CorsightAccountUpdateFormData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    account?: CorsightAccountData;
}>();

const emit = defineEmits(['close']);

const editCorsight = reactive({
    form: useForm<CorsightAccountUpdateFormData>({
        id: '',
        username: '',
        password: '',
        endpoint_secret: '',
        endpoint_url: '',
    }),
    processData: (account: CorsightAccountData) => {
        axios.post(credentials({ account: account.id }).url).then((response) => {
            editCorsight.form.password = response.data.password;
            editCorsight.form.endpoint_secret = response.data.endpoint_secret;
        });
    },
    onSubmit: () => {
        editCorsight.form.put(update({ account: editCorsight.form.id }).url, {
            preserveState: true,
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;

watch(
    () => props.account,
    (newData) => {
        if (newData?.id) {
            editCorsight.processData(newData);
            editCorsight.form.defaults({
                ...newData,
            });
        }

        editCorsight.form.reset();
        editCorsight.form.clearErrors();
    },
    { immediate: true },
);
</script>
<template>
    <ModalForm
        :open="props.show"
        @close="emit('close')"
        @submit="editCorsight.onSubmit"
        title="modal.action.update_corsight_account"
        confirmLabel="action.update"
        icon="lucide:scan-face"
        variant="default"
    >
        <div class="flex flex-col gap-5">
            <InputWithLabel
                v-model="editCorsight.form.username"
                type="text"
                label="form.label.username"
                placeholder="form.placeholder.username"
                :error="editCorsight.form.errors.username"
            />
            <InputWithLabel
                v-model="editCorsight.form.password"
                type="password"
                label="form.label.password"
                placeholder="form.placeholder.password"
                :error="editCorsight.form.errors.password"
            />
            <InputWithLabel
                v-model="editCorsight.form.endpoint_secret"
                type="password"
                label="form.label.endpoint_secret"
                placeholder="form.placeholder.endpoint_secret"
                :error="editCorsight.form.errors.endpoint_secret"
            />
            <InputWithLabel
                v-model="editCorsight.form.endpoint_url"
                type="text"
                label="form.label.endpoint_url"
                placeholder="form.placeholder.endpoint_url"
                :error="editCorsight.form.errors.endpoint_url"
            />
        </div>
    </ModalForm>
</template>
