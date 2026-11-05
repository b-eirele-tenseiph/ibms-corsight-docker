<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import InputWithLabelAndButton from '@/components/shared/inputs/InputWithLabelAndButton.vue';
import { ModalForm } from '@/components/shared/modals';
import { Button } from '@/components/ui/button';
import { generateToken, update } from '@/routes/admin/management/accounts/admins';
import { Role } from '@/types';
import { Admin, AdminForm } from '@/types/t-admin';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps<{
    show: boolean;
    roles: Role[];
    admin: Admin;
}>();

const form = useForm<AdminForm>({
    id: props.admin.id,
    name: props.admin.name,
    username: props.admin.username,
    email: props.admin.email,
    role: props.admin.roles?.[0]?.name ?? '',
    password: '',
    reset_link: '',
    change_password: false,
    generate_reset_link: false,
});

const showPasswordInput = ref(false);
const showResetLinkInput = ref(false);
const isGeneratingLink = ref(false);

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

function resetForm() {
    emit('close');
    form.reset();
    form.clearErrors();
}

function togglePasswordInput() {
    form.change_password = !form.change_password;
    if (!form.change_password) {
        form.reset('password');
        form.clearErrors('password');
    }
}

function toggleResetLinkInput() {
    form.generate_reset_link = !form.generate_reset_link;
    if (!form.generate_reset_link) {
        form.reset('reset_link');
        form.clearErrors('reset_link');
    }
}

function handleSubmit() {
    form.put(update(String(props.admin.id)).url, {
        onSuccess: resetForm,
    });
}

async function generateResetLink() {
    if (isGeneratingLink.value) return;

    isGeneratingLink.value = true;
    try {
        const url = generateToken(String(props.admin.id)).url;
        const response = await axios.put(url);

        form.reset_link = response.data.link;
        form.clearErrors('reset_link');

        await navigator.clipboard.writeText(response.data.link);
    } catch (error: any) {
        form.setError('reset_link', error.response?.data?.message || 'Failed to generate link.');
    } finally {
        isGeneratingLink.value = false;
    }
}

const isBlocked = computed(() => {
    // 1. Always block if the form is currently submitting
    if (form.processing) return true;

    // 2. Check if the "Change Password" section is active but the field is empty
    if (form.change_password && !form.password.trim()) {
        return true;
    }

    // 3. Check if "Generate Reset Link" section is active but no link has been generated yet
    if (form.generate_reset_link && !form.reset_link) {
        return true;
    }

    // 4. Finally, only enable if the form actually has changes (isDirty)
    // Note: Inertia's isDirty will be true if change_password or generate_reset_link
    // were toggled, so we just need to ensure the basic "has change" check.
    return !form.isDirty;
});
</script>

<template>
    <ModalForm
        :open="props.show"
        :title="'modal.action.update_admin_account'"
        icon="tabler:user-shield"
        :confirm-label="'action.update'"
        :closeOnOverlayClick="false"
        @close="resetForm"
        :isBlocked="isBlocked"
        @submit="handleSubmit"
        variant="default"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Admin Name -->
            <div class="grid gap-1.5">
                <InputWithLabel label="form.label.name" v-model="form.name" placeholder="form.placeholder.name" :error="form.errors.name" required />
            </div>

            <!-- Username -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.username"
                    v-model="form.username"
                    placeholder="form.placeholder.username"
                    :error="form.errors.username"
                    required
                />
            </div>

            <!-- Email -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.email_address"
                    v-model="form.email"
                    placeholder="form.placeholder.email_address"
                    :error="form.errors.email"
                    required
                />
            </div>

            <!-- Role Select -->
            <InputSelectWithLabel
                :items="roles"
                value-key="name"
                v-model="form.role"
                label="form.label.role"
                placeholder="form.placeholder.role"
                :error="form.errors.role"
            />

            <div class="flex justify-end">
                <Button variant="outline" class="bg-accent fluid-text-xs-sm cursor-pointer" @click.prevent="togglePasswordInput">
                    {{
                        showPasswordInput
                            ? $t('page.admin_management.action.cancel_change_password')
                            : $t('page.admin_management.action.change_password')
                    }}
                </Button>
            </div>

            <!-- Password Input -->
            <div v-if="form.change_password" class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.password"
                    type="password"
                    v-model="form.password"
                    placeholder="form.placeholder.password"
                    :error="form.errors.password"
                    required
                />
            </div>

            <div class="flex justify-end">
                <Button variant="outline" class="bg-accent fluid-text-xs-sm cursor-pointer" @click.prevent="toggleResetLinkInput">
                    {{
                        showResetLinkInput
                            ? $t('page.admin_management.action.cancel_reset_link')
                            : $t('page.admin_management.action.generate_reset_link')
                    }}
                </Button>
            </div>

            <!-- Generate Reset Link Input -->
            <div v-if="form.generate_reset_link" class="flex w-full">
                <InputWithLabelAndButton
                    label="form.label.reset_link"
                    v-model="form.reset_link"
                    placeholder="form.placeholder.reset_link"
                    :error="form.errors.reset_link"
                    readonly
                    @click="generateResetLink"
                />
            </div>
        </form>
    </ModalForm>
</template>
