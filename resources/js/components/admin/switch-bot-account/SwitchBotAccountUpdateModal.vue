<script setup lang="ts">
import { InputSelectWithLabel, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Badge } from '@/components/ui/badge';
import { credentials, showWebhook, update } from '@/routes/admin/management/switchbot/accounts';
import { Room, SwitchBotAccountData, SwitchBotAccountUpdateFormData } from '@/types';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

const props = defineProps<{
    show: boolean;
    room: Array<Room>;
    account?: SwitchBotAccountData;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const { t } = useI18n();

const editSwitchBot = reactive({
    form: useForm<SwitchBotAccountUpdateFormData>({
        id: '',
        access_token: '',
        secret_key: '',
        webhook_action: 'update',
        webhook_url: '',
    }),
    loadWebhookTimeout: '',
    useDefaultWebhook: () => {
        editSwitchBot.form.webhook_url = window.location.origin;
    },
    toggleDefault: computed(() => {
        return editSwitchBot.form.webhook_url !== window.location.origin;
    }),
    processData: (account: SwitchBotAccountData) => {
        axios
            .post(credentials({ account: account.id }).url)
            .then((response) => {
                editSwitchBot.form.access_token = response.data.token;
                editSwitchBot.form.secret_key = response.data.secret;
            })
            .finally(() => {
                editSwitchBot.loadWebhook();
            });
    },
    loadWebhook: () => {
        clearTimeout(editSwitchBot.loadWebhookTimeout);

        editSwitchBot.loadWebhookTimeout = setTimeout(() => {
            editSwitchBot.form.processing = true;

            axios
                .post(showWebhook.url(), {
                    token: editSwitchBot.form.access_token,
                    secret: editSwitchBot.form.secret_key,
                })
                .then((response) => {
                    editSwitchBot.form.webhook_action = response.data.action;
                    editSwitchBot.form.webhook_url = response.data.url;

                    if (editSwitchBot.form.webhook_action && editSwitchBot.form.webhook_url) {
                        toast.info(t('modal.hint.webhook_found'), {
                            classes: {
                                toast: 'border-l-3 border-l-info',
                                icon: 'text-info [&>svg]:size-4 xs:[&>svg]:size-5',
                            },
                        });
                    }
                })
                .finally(() => {
                    editSwitchBot.form.processing = false;
                });
        }, 1000);
    },
    onSubmit: () => {
        editSwitchBot.form.put(update({ account: editSwitchBot.form.id }).url, {
            preserveState: true,
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;

// Updates the form when the room prop changes (for reactivity when switching rooms)
watch(
    () => props.account,
    (newData) => {
        if (newData?.id) {
            editSwitchBot.processData(newData);
            editSwitchBot.form.defaults({
                ...newData,
            });
        }

        editSwitchBot.form.reset();
        editSwitchBot.form.clearErrors();
    },
    { immediate: true },
);
</script>
<template>
    <ModalForm
        :open="props.show"
        @close="emit('close')"
        @submit="editSwitchBot.onSubmit"
        title="modal.action.update_switch_bot_account"
        confirmLabel="action.update"
        icon="material-symbols:account-box-outline"
        variant="default"
    >
        <div class="flex flex-col gap-5">
            <InputSelectWithLabel
                :items="room"
                v-model="editSwitchBot.form.room.id"
                label="form.label.room_name"
                placeholder="form.placeholder.room_name"
                disabled
            />

            <div class="relative">
                <Icon icon="line-md:loading-twotone-loop" v-if="editSwitchBot.form.processing" class="absolute end-0 top-0 m-auto text-lg" />
                <InputTextAreaWithLabel
                    v-model="editSwitchBot.form.access_token"
                    label="form.label.access_token"
                    placeholder="form.placeholder.access_token"
                    @input="editSwitchBot.loadWebhook"
                    :error="editSwitchBot.form.errors.access_token"
                    :disabled="editSwitchBot.form.processing"
                    :class="{ 'opacity-60': editSwitchBot.form.processing }"
                />
            </div>

            <div class="relative">
                <Icon icon="line-md:loading-twotone-loop" v-if="editSwitchBot.form.processing" class="absolute end-0 top-0 m-auto text-lg" />
                <InputTextAreaWithLabel
                    v-model="editSwitchBot.form.secret_key"
                    label="form.label.secret_key"
                    placeholder="form.placeholder.secret_key"
                    @input="editSwitchBot.loadWebhook"
                    :error="editSwitchBot.form.errors.secret_key"
                    :disabled="editSwitchBot.form.processing"
                    :class="{ 'opacity-60': editSwitchBot.form.processing }"
                />
            </div>

            <div class="relative justify-end">
                <Badge
                    v-if="editSwitchBot.toggleDefault"
                    @click="editSwitchBot.useDefaultWebhook"
                    class="absolute end-0 -top-2 z-10 cursor-pointer"
                    variant="secondary"
                >
                    {{ $t('label.default') }}
                </Badge>
                <InputWithLabel
                    v-model="editSwitchBot.form.webhook_url"
                    label="form.label.webhook"
                    placeholder="form.placeholder.webhook"
                    type="text"
                    :error="editSwitchBot.form.errors.webhook_url"
                />
            </div>
        </div>
    </ModalForm>
</template>
