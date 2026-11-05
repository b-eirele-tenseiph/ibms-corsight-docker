<script setup lang="ts">
import { InputSelectWithLabel, InputSingleImageFile, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Switch } from '@/components/ui/switch';
import { store } from '@/routes/crew/foods';
import type { FoodCategory } from '@/types/t-food';
import { useForm } from '@inertiajs/vue3';

// Props
const props = defineProps<{
    isOpen: boolean;
    categories: FoodCategory[];
}>();

// Emits
const emit = defineEmits<{
    (e: 'close'): void;
}>();

// Form
const form = useForm({
    name: '',
    description: '',
    category_id: null,
    price: undefined,
    image: null as File | null,
    available: true,
});

// Submit
function onSubmit() {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            onCancel();
        },
    });
}

function onCancel() {
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <ModalForm
        :open="isOpen"
        title="modal.action.add_menu"
        confirmLabel="action.create"
        :isProcessing="form.processing"
        icon="ph:bowl-food-duotone"
        @submit="onSubmit"
        @close="onCancel"
        :is-blocked="form.processing"
        :closeOnOverlayClick="false"
        variant="default"
    >
        <div class="max-h-[60vh] space-y-4 pr-2">
            <!-- Image Upload -->
            <div class="flex justify-center gap-y-1">
                <InputSingleImageFile v-model="form.image" :error="form.errors.image" />
            </div>

            <!-- Food Name -->
            <InputWithLabel v-model="form.name" label="form.label.food_name" placeholder="form.placeholder.food_name" :error="form.errors.name" />

            <!-- Description -->
            <InputTextAreaWithLabel
                v-model="form.description"
                type="textarea"
                label="form.label.description"
                placeholder="form.placeholder.food_description"
                :error="form.errors.description"
            />

            <!-- Availability Toggle -->
            <div class="flex items-center gap-2">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700"> {{ $t('form.label.availability') }} </label>
                    <div class="flex flex-row gap-2">
                        <Switch v-model="form.available" size="base" :disabled="form.processing" />

                        <span class="text-sm text-gray-600">{{ $t('status.in_stock') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row">
                <!-- Category -->
                <div class="w-full sm:w-1/2">
                    <InputSelectWithLabel
                        :items="props.categories"
                        v-model="form.category_id"
                        label="form.label.category"
                        placeholder="form.placeholder.category"
                        :value-key="'id'"
                        :label-key="'name'"
                        :error="form.errors.category_id"
                    />
                </div>

                <!-- Price -->
                <div class="w-full sm:w-1/2">
                    <InputWithLabel
                        v-model="form.price"
                        label="form.label.price"
                        placeholder="form.placeholder.price"
                        min="0"
                        type="number"
                        :error="form.errors.price"
                    />
                </div>
            </div>
        </div>
    </ModalForm>
</template>
