<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalShowForm } from '@/components/shared/modals';
import { credentials } from '@/routes/admin/management/corsight/accounts';
import { CorsightAccountData, CorsightAccountShowCredData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, reactive, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    account?: Pick<CorsightAccountData, 'id'>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const showCorsight = reactive({
    form: useForm<CorsightAccountShowCredData>({
        username: '',
        password: '',
        endpoint_secret: '',
        endpoint_url: '',
    }),
    envSnippet: computed(() =>
        [
            `FORTIFY_USERNAME=${showCorsight.form.username}`,
            `IBMS_ENDPOINT_URL=${showCorsight.form.endpoint_url ?? ''}`,
            `IBMS_ENDPOINT_SECRET=${showCorsight.form.endpoint_secret}`,
        ].join('\n'),
    ),
    processData: () => {
        axios.post(credentials({ account: props.account!.id }).url).then((response) => {
            Object.assign(showCorsight.form, {
                username: response.data.username,
                password: response.data.password,
                endpoint_secret: response.data.endpoint_secret,
                endpoint_url: response.data.endpoint_url,
            });
        });
    },
}) as any;

watch(
    () => props.account,
    (newData) => {
        if (newData?.id) {
            showCorsight.processData(newData);
        }
    },
    { immediate: true },
);
</script>
<template>
    <ModalShowForm :open="show" @close="emit('close')" title="Auth Credentials" icon="lucide:shield-check">
        <div class="flex flex-col gap-4">
            <InputWithLabel
                v-model="showCorsight.form.password"
                label="form.label.password"
                placeholder="form.placeholder.password"
                type="password"
                autocomplete="off"
                readonly
                tabindex="-1"
            />

            <div class="bg-secondary/30 rounded-lg border p-4">
                <p class="text-sm font-semibold">{{ $t('page.corsight_account.env_snippet.title') }}</p>
                <p class="text-muted-foreground mb-3 text-xs">{{ $t('page.corsight_account.env_snippet.subtitle') }}</p>
                <pre class="bg-background overflow-x-auto rounded p-3 font-mono text-xs leading-relaxed">{{ showCorsight.envSnippet }}</pre>
            </div>
        </div>
    </ModalShowForm>
</template>
