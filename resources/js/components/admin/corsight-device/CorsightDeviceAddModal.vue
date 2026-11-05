<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { store } from '@/routes/admin/management/corsight/devices';
import { CorsightDeviceCreateFormData, CorsightDeviceProps } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    show: boolean;
    rooms: CorsightDeviceProps['rooms'];
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

const addCorsightDevice = reactive({
    form: useForm<CorsightDeviceCreateFormData>({
        name: '',
        role: 'general',
        room_id: '',
        room_area_id: '',
    }),
    // Room areas available for the currently selected room only
    roomAreaItems: computed(() => {
        const room = props.rooms.find((r: any) => r.id === addCorsightDevice.form.room_id);
        return room?.room_areas ?? [];
    }),
    onRoomChange: (value: string) => {
        addCorsightDevice.form.room_area_id = '';
        addCorsightDevice.form.room_id = value;
    },
    onSubmit: () => {
        addCorsightDevice.form.post(store.url(), {
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
            addCorsightDevice.form.reset();
            addCorsightDevice.form.clearErrors();
        }
    },
);
</script>

<template>
    <ModalForm
        :open="show"
        :isBlocked="!addCorsightDevice.form.isDirty"
        @close="emit('close')"
        @submit="addCorsightDevice.onSubmit"
        title="modal.action.add_corsight_device"
        confirmLabel="action.create"
        icon="lucide:scan-face"
    >
        <div class="flex flex-col gap-5">
            <div>
                <InputWithLabel
                    v-model="addCorsightDevice.form.name"
                    type="text"
                    label="form.label.device_name"
                    placeholder="form.placeholder.enter_device_name"
                    :error="addCorsightDevice.form.errors.name"
                />
                <p class="mt-1 text-xs text-muted-foreground">{{ $t('page.corsight_device.hint.name_match') }}</p>
            </div>
            <InputSelectWithLabel
                v-model="addCorsightDevice.form.role"
                :items="roleItems"
                label="form.label.role"
                placeholder="form.placeholder.role"
                :error="addCorsightDevice.form.errors.role"
            />
            <InputSelectWithLabel
                v-model="addCorsightDevice.form.room_id"
                :items="rooms"
                label="form.label.room_name"
                placeholder="form.placeholder.room_name"
                :error="addCorsightDevice.form.errors.room_id"
                @update:model-value="addCorsightDevice.onRoomChange"
            />
            <InputSelectWithLabel
                v-model="addCorsightDevice.form.room_area_id"
                :items="addCorsightDevice.roomAreaItems"
                label="form.label.room_area"
                placeholder="form.placeholder.select_room_area"
                :error="addCorsightDevice.form.errors.room_area_id"
                :disabled="!addCorsightDevice.form.room_id"
            />
        </div>
    </ModalForm>
</template>
