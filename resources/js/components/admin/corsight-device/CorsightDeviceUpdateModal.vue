<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/admin/management/corsight/devices';
import { CorsightDeviceData, CorsightDeviceProps, CorsightDeviceUpdateFormData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    show: boolean;
    rooms: CorsightDeviceProps['rooms'];
    device?: CorsightDeviceData;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const { t } = useI18n();

const roleItems = computed(() => [
    { id: 'general', name: t('page.corsight_person.role.general') },
    { id: 'entry_point', name: t('page.corsight_person.role.entry_point') },
    { id: 'exit_point', name: t('page.corsight_person.role.exit_point') },
    { id: 'passage', name: t('page.corsight_person.role.passage') },
]);

const editCorsightDevice = reactive({
    form: useForm<CorsightDeviceUpdateFormData>({
        id: '',
        name: '',
        role: 'general',
        room_id: '',
        room_area_id: '',
    }),
    // Room areas available for the currently selected room only
    roomAreaItems: computed(() => {
        const room = props.rooms.find((r: any) => r.id === editCorsightDevice.form.room_id);
        return room?.room_areas ?? [];
    }),
    onRoomChange: (value: string) => {
        editCorsightDevice.form.room_area_id = '';
        editCorsightDevice.form.room_id = value;
    },
    onSubmit: () => {
        editCorsightDevice.form.put(update({ device: editCorsightDevice.form.id }).url, {
            preserveState: true,
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;

watch(
    () => props.device,
    (newData) => {
        if (newData?.id) {
            editCorsightDevice.form.defaults({
                id: newData.id,
                name: newData.name,
                role: newData.role ?? 'general',
                room_id: newData.room?.id ?? '',
                room_area_id: newData.room_area?.id ?? '',
            });
        }

        editCorsightDevice.form.reset();
        editCorsightDevice.form.clearErrors();
    },
    { immediate: true },
);
</script>
<template>
    <ModalForm
        :open="props.show"
        @close="emit('close')"
        @submit="editCorsightDevice.onSubmit"
        title="modal.action.update_corsight_device"
        confirmLabel="action.update"
        icon="lucide:scan-face"
        variant="default"
    >
        <div class="flex flex-col gap-5">
            <div>
                <InputWithLabel
                    v-model="editCorsightDevice.form.name"
                    type="text"
                    label="form.label.device_name"
                    placeholder="form.placeholder.enter_device_name"
                    :error="editCorsightDevice.form.errors.name"
                />
                <p class="mt-1 text-xs text-muted-foreground">{{ $t('page.corsight_device.hint.name_match') }}</p>
            </div>
            <InputSelectWithLabel
                v-model="editCorsightDevice.form.role"
                :items="roleItems"
                label="form.label.role"
                placeholder="form.placeholder.role"
                :error="editCorsightDevice.form.errors.role"
            />
            <InputSelectWithLabel
                v-model="editCorsightDevice.form.room_id"
                :items="rooms"
                label="form.label.room_name"
                placeholder="form.placeholder.room_name"
                :error="editCorsightDevice.form.errors.room_id"
                @update:model-value="editCorsightDevice.onRoomChange"
            />
            <InputSelectWithLabel
                v-model="editCorsightDevice.form.room_area_id"
                :items="editCorsightDevice.roomAreaItems"
                label="form.label.room_area"
                placeholder="form.placeholder.select_room_area"
                :error="editCorsightDevice.form.errors.room_area_id"
                :disabled="!editCorsightDevice.form.room_id"
            />
        </div>
    </ModalForm>
</template>
