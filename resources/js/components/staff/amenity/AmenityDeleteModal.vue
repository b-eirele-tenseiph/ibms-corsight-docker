<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/staff/amenity_management';
import { Amenity } from '@/types';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close']);

const props = defineProps<{
    open: boolean;
    amenity: Amenity | null;
}>();

// Delete Amenity
const submitDeleteAmenity = () => {
    if (!props.amenity) return;

    useForm({}).delete(destroy({ amenity: props.amenity.id }).url, {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>
<template>
    <ModalForm
        :open="open"
        title="modal.action.delete_amenity"
        icon="lucide:triangle-alert"
        confirmLabel="action.delete"
        variant="destructive"
        @close="$emit('close')"
        @submit="submitDeleteAmenity"
        iconColor="text-error"
    >
        <form @submit.prevent="submitDeleteAmenity" class="flex flex-col gap-y-4">
            <div class="text-sm font-bold">
                <p>
                    {{ $t('modal.confirm.delete_amenity') }}
                </p>
            </div>

            <div class="flex flex-col gap-y-1.5 text-sm">
                <p class="text-error text-sm font-semibold">
                    {{ $t('modal.confirm.warning') }}
                </p>
                <p class="font-regular">
                    <span class="font-semibold">{{ $t('form.label.amenity_title') }}:</span>
                    {{ props.amenity?.title }}
                </p>
            </div>
        </form>
    </ModalForm>
</template>
