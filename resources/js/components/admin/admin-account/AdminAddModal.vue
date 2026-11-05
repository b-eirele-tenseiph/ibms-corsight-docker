<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { store } from '@/routes/admin/management/accounts/admins';
import { AdminForm, Role } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    roles: Role[];
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const form = useForm<AdminForm>({
    name: '',
    username: '',
    email: '',
    password: '',
    role: '',
});

function resetForm() {
    emit('close');
    form.reset();
    form.clearErrors();
}

function handleSubmit() {
    form.post(store.url(), {
        onSuccess: () => {
            resetForm();
        },
    });
}

const isBlocked = computed(() => form.processing || !form.isDirty);
</script>

<template>
    <ModalForm
        :open="props.show"
        :title="'modal.action.add_admin_account'"
        icon="tabler:user-shield"
        :confirm-label="'action.create'"
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

            <!-- Password -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    :type="'password'"
                    label="form.label.password"
                    v-model="form.password"
                    placeholder="form.placeholder.password"
                    :error="form.errors.password"
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
        </form>
    </ModalForm>
</template>
