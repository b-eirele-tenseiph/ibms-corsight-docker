<script setup lang="ts">
import { InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalShowForm } from '@/components/shared/modals';
import { credentials } from '@/routes/admin/management/switchbot/accounts';
import { SwitchBotAccountData, SwitchBotAccountShowCredData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    account?: Pick<SwitchBotAccountData, 'id'>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const showSwitchbot = reactive({
    form: useForm<SwitchBotAccountShowCredData>({
        password: '',
        access_token: '',
        secret_key: '',
    }),
    processData: () => {
        axios.post(credentials({ account: props.account!.id }).url).then((response) => {
            Object.assign(showSwitchbot.form, {
                access_token: response.data.token,
                secret_key: response.data.secret,
                password: response.data.password,
            });
        });
    },
}) as any;

watch(
    () => props.account,
    (newData) => {
        if (newData?.id) {
            showSwitchbot.processData(newData);
        }
    },
    { immediate: true },
);
</script>
<template>
    <ModalShowForm :open="show" @close="emit('close')" title="Auth Credentials" icon="lucide:shield-check">
        <div class="flex flex-col gap-2">
            <InputWithLabel
                v-model="showSwitchbot.form.password"
                label="form.label.password"
                placeholder="form.placeholder.password"
                type="password"
                autocomplete="password"
                readonly
                tabindex="-1"
            />
            <InputTextAreaWithLabel
                v-model="showSwitchbot.form.access_token"
                label="form.label.access_token"
                placeholder="form.placeholder.access_token"
                autocomplete="access_token"
                readonly
                tabindex="-1"
            />
            <InputTextAreaWithLabel
                v-model="showSwitchbot.form.secret_key"
                label="form.label.secret_key"
                placeholder="form.placeholder.secret_key"
                autocomplete="secret_key"
                readonly
                tabindex="-1"
            />
        </div>
    </ModalShowForm>
</template>
