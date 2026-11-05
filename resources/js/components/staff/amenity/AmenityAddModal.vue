<script setup lang="ts">
import { InputAttributeStacker, InputImageFile, InputSelectWithLabel, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { store } from '@/routes/staff/amenity_management';
import { AmenityAttribute, AmenityCategory } from '@/types';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'submit']);

defineProps<{
    open: boolean;
    amenityCategories: AmenityCategory[];
}>();

const addAmenityForm = useForm({
    title: '',
    description: '',
    category_id: '',
    photo: [] as File[],
    attribute: [] as AmenityAttribute[],
});

const submit = () => {
    addAmenityForm.post(store.url(), {
        forceFormData: true,
        onSuccess: () => {
            addAmenityForm.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.add_amenity"
        icon="iconoir:accessibility"
        variant="default"
        confirmLabel="action.create"
        @submit="submit"
        @close="$emit('close')"
        :isBlocked="addAmenityForm.processing"
        :isCancelBlocked="addAmenityForm.processing"
    >
        <fieldset :disabled="addAmenityForm.processing" class="flex flex-col gap-y-4">
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.amenity_title"
                    placeholder="form.placeholder.amenity_title"
                    type="text"
                    v-model:modelValue="addAmenityForm.title"
                    :error="addAmenityForm.errors.title"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputTextAreaWithLabel
                    label="form.label.amenity_description"
                    placeholder="form.placeholder.amenity_description"
                    v-model:modelValue="addAmenityForm.description"
                    :error="addAmenityForm.errors.description"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputSelectWithLabel
                    label="form.label.amenity_category"
                    placeholder="form.placeholder.amenity_category"
                    :items="amenityCategories"
                    v-model:modelValue="addAmenityForm.category_id"
                    :error="addAmenityForm.errors.category_id"
                />
            </div>

            <div class="flex flex-col gap-y-1">
                <InputImageFile
                    label="form.label.amenity_photos"
                    placeholder="form.placeholder.amenity_photos"
                    v-model="addAmenityForm.photo"
                    :getPreviewUrl="(photo) => `${photo.file_path}`"
                    :error="addAmenityForm.errors.photo"
                    :maxItems="5"
                    :hint="'modal.hint.amenity_photos'"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputAttributeStacker
                    v-model="addAmenityForm.attribute"
                    stackLabel="form.label.additional_info"
                    stackAction="action.show_more_or_less"
                    stackFallback="page.amenities.state.empty_attribute"
                    stackFormLabel="form.label.add_new_info"
                    stackFormHint="modal.description.amenity_description"
                    iconStyling="text-primary-link-text"
                />
            </div>
        </fieldset>
    </ModalForm>
</template>
