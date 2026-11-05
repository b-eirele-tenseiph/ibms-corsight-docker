<script setup lang="ts">
import { InputAttributeStacker, InputImageFile, InputSelectWithLabel, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/staff/amenity_management';
import { Amenity, AmenityAttribute, AmenityCategory, AmenityPhoto, AmenityPhotoOrFile } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const emit = defineEmits(['close']);
const props = defineProps<{
    open: boolean;
    amenity: Amenity | null;
    amenityCategories: AmenityCategory[];
}>();

const photos = ref<(AmenityPhoto | File)[]>([]);

const updateAmenityForm = useForm({
    id: '',
    title: '',
    description: '',
    category_id: '',
    attributes: [] as AmenityAttribute[],
    new_photos: [] as Array<AmenityPhotoOrFile>,
    existing_photo_ids: [] as string[],
});

// Watch for `amenity` prop updates and rehydrate form
watch(
    () => props.amenity,
    (amenity) => {
        if (!amenity) return;

        updateAmenityForm.id = amenity.id ?? '';
        updateAmenityForm.title = amenity.title ?? '';
        updateAmenityForm.description = amenity.description ?? '';
        updateAmenityForm.category_id = amenity.category_id ?? '';
        updateAmenityForm.attributes = amenity.attributes ?? [];

        photos.value = (amenity.photos ?? []).slice() ?? [];
    },
    { immediate: true },
);

// Watch for modal open state; clear validation errors when modal is closed
watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            updateAmenityForm.clearErrors();
        }
    },
);

// Add this watch to keep the form data synced automatically
watch(
    photos,
    (newPhotosValue) => {
        updateAmenityForm.existing_photo_ids = newPhotosValue.filter((p): p is AmenityPhoto => !(p instanceof File)).map((p) => p.id);
        updateAmenityForm.new_photos = newPhotosValue.filter((p): p is File => p instanceof File);
    },
    { deep: true },
);

const submitUpdateAmenity = () => {
    updateAmenityForm.existing_photo_ids = (photos.value as (AmenityPhoto | File)[])
        .filter((p): p is AmenityPhoto => !(p instanceof File))
        .map((p) => p.id);

    updateAmenityForm.new_photos = (photos.value as (AmenityPhoto | File)[]).filter((photo): photo is AmenityPhoto => photo instanceof File);

    updateAmenityForm.post(update({ amenity: updateAmenityForm.id }).url, {
        method: 'put',
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        :isBlocked="updateAmenityForm.processing"
        title="modal.action.update_amenity"
        icon="iconoir:accessibility"
        confirmLabel="action.update"
        variant="default"
        @submit="submitUpdateAmenity"
        @close="$emit('close')"
        :isCancelBlocked="updateAmenityForm.processing"
    >
        <form class="flex flex-col gap-y-4" @submit.prevent @keydown.enter.prevent>
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.amenity_title"
                    placeholder="form.placeholder.amenity_title"
                    type="text"
                    v-model:modelValue="updateAmenityForm.title"
                    :error="updateAmenityForm.errors.title"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputTextAreaWithLabel
                    label="form.label.amenity_description"
                    placeholder="form.placeholder.amenity_description"
                    v-model:modelValue="updateAmenityForm.description"
                    :error="updateAmenityForm.errors.description"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputSelectWithLabel
                    label="form.label.amenity_category"
                    placeholder="form.placeholder.amenity_category"
                    :items="amenityCategories"
                    v-model:modelValue="updateAmenityForm.category_id"
                    :error="updateAmenityForm.errors.category_id"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputImageFile
                    label="form.label.amenity_photos"
                    placeholder="form.placeholder.amenity_photos"
                    v-model="photos"
                    :getPreviewUrl="(photo) => `${photo.file_path}`"
                    :error="updateAmenityForm.errors.new_photos"
                    :maxItems="5"
                    :hint="'modal.hint.amenity_photos'"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputAttributeStacker
                    v-model="updateAmenityForm.attributes"
                    stackLabel="form.label.additional_info"
                    stackAction="action.show_more_or_less"
                    stackFallback="page.amenities.state.empty_attribute"
                    stackFormLabel="form.label.add_new_info"
                    stackFormHint="modal.description.amenity_description"
                    iconStyling="text-primary-link-text"
                />
            </div>
        </form>
    </ModalForm>
</template>
