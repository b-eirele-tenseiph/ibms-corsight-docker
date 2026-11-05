<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Label } from '@/components/ui/label';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { update } from '@/routes/admin/management/lorawan/gateways';
import { Floor, LorawanGatewayFormData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    gateway: LorawanGatewayFormData & { id: string };
    show: boolean;
    connections: Array<any>;
    floors: Array<Floor>;
}>();

const form = useForm<LorawanGatewayFormData>({
    ip_address: props.gateway.ip_address,
    mac_address: props.gateway.mac_address,
    connection: props.gateway.connection,
    floor_id: Number(props.gateway.floor_id),
});

const loading = ref(false);

function resetForm() {
    form.reset();
    form.clearErrors();
}

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

function handleSubmit() {
    loading.value = true;
    form.put(update({ gateway: props.gateway.id }).url, {
        onSuccess: () => {
            emit('close');
            resetForm();
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

const isBlocked = computed(() => loading.value || !form.isDirty);

watch(
    () => props.gateway,
    (gw) => {
        form.defaults({
            ip_address: gw.ip_address,
            mac_address: gw.mac_address,
            connection: gw.connection,
            floor_id: Number(gw.floor_id),
        });
        form.reset();
    },
    { immediate: true },
);
</script>

<template>
    <ModalForm
        :open="props.show"
        :title="'modal.action.update_lorawan_gateway'"
        icon="grommet-icons:gateway"
        :confirm-label="'action.update'"
        :closeOnOverlayClick="false"
        @close="emit('close')"
        :isBlocked="isBlocked"
        @submit="handleSubmit"
        variant="default"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Room Name -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.ip_address"
                    v-model="form.ip_address"
                    placeholder="form.placeholder.ip_address"
                    :error="form.errors.ip_address"
                    required
                />
            </div>

            <!-- Room Number -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.mac_address"
                    v-model="form.mac_address"
                    placeholder="form.placeholder.mac_address"
                    :error="form.errors.mac_address"
                    required
                />
            </div>
            <!-- Floor Select -->
            <InputSelectWithLabel
                :items="floors"
                v-model="form.floor_id"
                label="form.label.floor_name"
                placeholder="form.placeholder.select_floor"
                :error="form.errors.floor_id"
            />

            <Label class="fluid-text-xs-sm">{{ $t('form.label.connection') }}</Label>
            <ToggleGroup v-model="form.connection" type="single" size="sm" class="mb-2 space-x-2">
                <template v-for="(connection, index) in connections" :key="index">
                    <ToggleGroupItem class="px-3" :value="connection">
                        {{ connection }}
                    </ToggleGroupItem>
                </template>
            </ToggleGroup>
            <div v-if="form.errors.connection" class="text-sm text-red-500">
                {{ form.errors.connection }}
            </div>
        </form>
    </ModalForm>
</template>
