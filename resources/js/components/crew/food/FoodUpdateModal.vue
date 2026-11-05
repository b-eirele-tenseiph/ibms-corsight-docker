<script setup lang="ts">
import { InputSelectWithLabel, InputSingleImageFile, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Switch } from '@/components/ui/switch';
import { update } from '@/routes/crew/foods';
import type { Food, FoodCategory } from '@/types/t-food';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    categories: FoodCategory[];
    food: Food | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

// Base form (will be initialized from props.food by watcher)
const form = useForm({
    name: '' as string,
    description: '' as string,
    category_id: null as number | null,
    price: undefined as number | undefined,
    image: null as File | null,
    image_path: '' as string,
    available: true as boolean,
});

// Fill the form whenever modal opens with a food
watch(
    () => props.food,
    (food) => {
        if (!food) return;
        form.name = food.name ?? '';
        form.description = food.description ?? '';
        form.category_id = (food as any).category_id ?? null;
        form.price = (food.price as unknown as number) ?? undefined;
        form.available = food.available;
        form.image_path = food.image;

        form.clearErrors();
    },
    { immediate: true },
);

// Cancel
function onCancel() {
    form.reset();
    form.clearErrors();
    emit('close');
}

// Submit (PUT to update route)
function onSubmit() {
    if (!props.food) return;

    form.post(update({ food: props.food.id }).url, {
        method: 'put',
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
}
</script>

<template>
    <ModalForm
        :open="props.isOpen"
        title="modal.action.edit_menu"
        confirmLabel="action.update"
        :is-blocked="form.processing"
        @close="onCancel"
        @submit="onSubmit"
        icon="ph:bowl-food-duotone"
        variant="default"
    >
        <div class="max-h:[60vh] space-y-4 pr-2">
            <!-- Image Upload -->
            <div class="flex flex-col items-center justify-center gap-y-2">
                <InputSingleImageFile
                    v-model="form.image"
                    :photo_file_path="form.image_path"
                    :error="form.errors.image"
                    @update:error="form.errors.image = $event ?? undefined"
                />
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

            <!-- Availability -->
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
