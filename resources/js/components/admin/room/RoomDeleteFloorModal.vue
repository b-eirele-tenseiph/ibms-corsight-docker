<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { floorDelete } from '@/routes/admin/management/rooms';
import { router } from '@inertiajs/vue3';
import { computed, ref, watchEffect } from 'vue';

const props = defineProps<{
    open: boolean;
    floorId?: number;
    floorName?: string;
    used?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const localErrors = ref<string[]>([]);

// Close modal and reset error state
function close() {
    emit('update:open', false);
    localErrors.value = [];
}

// Handle deletion confirmation
function confirm() {
    if (!props.floorId) return;

    // Prevent deletion if floor is used
    if (props.used) {
        localErrors.value = ['modal.alert.floor_error'];
        return;
    }

    router.delete(floorDelete(props.floorId).url, {
        onSuccess: close,
        onError: (err) => {
            localErrors.value = err.floor ? [err.floor] : ['errors.unknown']; // Optional: standardize unknown error key
        },
    });
}

// Watch for open state and pre-fill errors if floor is used
watchEffect(() => {
    if (props.open && props.used) {
        localErrors.value = ['modal.alert.room_assigned'];
    }
});

const isBlocked = computed(() => localErrors.value.length > 0);
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.delete_floor"
        icon="lucide:triangle-alert"
        :confirmLabel="'action.delete'"
        :closeOnOverlayClick="false"
        variant="destructive"
        :blockers="localErrors"
        :isBlocked="isBlocked"
        @close="close"
        @submit="confirm"
    >
        <div class="flex flex-col space-y-2">
            <label class="text-foreground fluid-text-xs-sm font-bold">
                {{ $t('modal.confirm.delete_floor') }}
            </label>
            <label class="text-destructive fluid-text-xs-sm py-2 font-semibold">
                {{ $t('modal.confirm.warning') }}
            </label>
            <label class="fluid-text-xs-sm">
                <span class="text-muted-foreground font-semibold">{{ $t('form.label.floor_name') }}:</span>
                <span class="text-foreground ml-1 font-medium">{{ floorName || 'Untitled' }}</span>
            </label>
        </div>

        <!-- Blocker Message -->
        <div v-if="localErrors.length" class="bg-accent mt-4 rounded-md border p-3">
            <p class="text-destructive fluid-text-xs-sm mb-2 font-bold">
                {{ $t('modal.alert.floor_error') }}
            </p>
            <ul class="fluid-text-xs-sm list-disc pl-5">
                <li v-for="(reason, index) in localErrors" :key="index">
                    {{ $t(reason) }}
                </li>
            </ul>
        </div>
    </ModalForm>
</template>
