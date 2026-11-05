<script setup lang="ts">
import { InputSelectWithLabel, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Badge } from '@/components/ui/badge';
import { showWebhook, store } from '@/routes/admin/management/switchbot/accounts';
import { Room, SwitchBotAccountCreateFormData } from '@/types';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

const props = defineProps<{
    show: boolean;
    unlinked_room: Array<Room>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const { t } = useI18n();

const addSwitchbot = reactive({
    form: useForm<SwitchBotAccountCreateFormData>({
        room_id: '',
        email: '',
        password: '',
        access_token: '',
        secret_key: '',
        webhook_action: 'store',
        webhook_url: '',
    }),
    loadWebhookTimeout: null,
    useDefaultWebhook: () => {
        addSwitchbot.form.webhook_url = window.location.origin;
    },
    toggleDefault: computed(() => {
        return addSwitchbot.form.webhook_url != window.location.origin;
    }),
    loadWebhook: () => {
        clearTimeout(addSwitchbot.loadWebhookTimeout);

        addSwitchbot.loadWebhookTimeout = setTimeout(() => {
            addSwitchbot.form.processing = true;

            axios
                .post(showWebhook.url(), {
                    token: addSwitchbot.form.access_token,
                    secret: addSwitchbot.form.secret_key,
                })
                .then((response) => {
                    addSwitchbot.form.webhook_action = response.data.action;
                    addSwitchbot.form.webhook_url = response.data.url;

                    if (addSwitchbot.form.webhook_action && addSwitchbot.form.webhook_url) {
                        toast.info(t('modal.hint.webhook_found'), {
                            classes: {
                                toast: 'border-l-3 border-l-info',
                                icon: 'text-info [&>svg]:size-4 xs:[&>svg]:size-5',
                            },
                        });
                    }
                })
                .finally(() => {
                    addSwitchbot.form.processing = false;
                });
        }, 1000);
    },
    onSubmit: () => {
        addSwitchbot.form.post(store.url(), {
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
            addSwitchbot.form.reset();
            addSwitchbot.form.clearErrors();
        }
    },
);
</script>

<template>
    <ModalForm
        :open="show"
        :isBlocked="!addSwitchbot.form.isDirty"
        @close="emit('close')"
        @submit="addSwitchbot.onSubmit"
        title="modal.action.add_switch_bot_account"
        confirmLabel="action.create"
        icon="material-symbols:account-box-outline"
    >
        <div class="flex flex-col gap-5">
            <InputSelectWithLabel
                :items="unlinked_room"
                v-model="addSwitchbot.form.room_id"
                label="form.label.room_name"
                placeholder="form.placeholder.room_name"
                :error="addSwitchbot.form.errors.room_id"
            />
            <InputWithLabel
                v-model="addSwitchbot.form.email"
                type="email"
                label="form.label.email_address"
                placeholder="form.placeholder.email_address"
                :error="addSwitchbot.form.errors.email"
            />
            <InputWithLabel
                v-model="addSwitchbot.form.password"
                type="password"
                label="form.label.password"
                placeholder="form.placeholder.password"
                :error="addSwitchbot.form.errors.password"
            />

            <div class="relative">
                <Icon icon="line-md:loading-twotone-loop" v-if="addSwitchbot.form.processing" class="absolute end-0 top-0 m-auto text-lg" />
                <InputTextAreaWithLabel
                    v-model="addSwitchbot.form.access_token"
                    label="form.label.access_token"
                    placeholder="form.placeholder.access_token"
                    @input="addSwitchbot.loadWebhook"
                    :error="addSwitchbot.form.errors.access_token"
                    :disabled="addSwitchbot.form.processing"
                    :class="{ 'opacity-60': addSwitchbot.form.processing }"
                />
            </div>

            <div class="relative">
                <Icon icon="line-md:loading-twotone-loop" v-if="addSwitchbot.form.processing" class="absolute end-0 top-0 m-auto text-lg" />
                <InputTextAreaWithLabel
                    v-model="addSwitchbot.form.secret_key"
                    label="form.label.secret_key"
                    placeholder="form.placeholder.secret_key"
                    @input="addSwitchbot.loadWebhook"
                    :error="addSwitchbot.form.errors.secret_key"
                    :disabled="addSwitchbot.form.processing"
                    :class="{ 'opacity-60': addSwitchbot.form.processing }"
                />
            </div>

            <div class="relative justify-end">
                <Badge
                    v-if="addSwitchbot.toggleDefault"
                    @click="addSwitchbot.useDefaultWebhook"
                    class="absolute end-0 -top-2 z-10 cursor-pointer"
                    variant="secondary"
                >
                    {{ $t('label.default') }}
                </Badge>
                <InputWithLabel
                    v-model="addSwitchbot.form.webhook_url"
                    label="form.label.webhook"
                    placeholder="form.placeholder.webhook"
                    type="text"
                    :error="addSwitchbot.form.errors.webhook_url"
                />
            </div>
        </div>
    </ModalForm>
</template>
