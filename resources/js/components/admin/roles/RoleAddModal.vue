<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import { store } from '@/routes/admin/management/settings/roles';
import type { AddRoleForm, Permission, Role } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    roles: Role[];
    permissions: Permission[];
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

// Form state
const form = useForm<AddRoleForm>({
    name: '' as string | number,
    guard_name: null,
    permissions: [],
});

// Computed list of unique guards
const uniqueGuards = computed(() => Array.from(new Set(props.permissions.map((p) => p.guard_name))));

// UI Refs & reactive dropdown
const permissionInput = ref('');
const permissionInputRef = ref<HTMLElement | null>(null);
const showPermissionDropdown = ref(false);

// Dropdown positioning
const dropdownTop = ref(0);
const dropdownLeft = ref(0);
const dropdownWidth = ref(0);

function positionDropdown(el: HTMLElement) {
    const rect = el.getBoundingClientRect();
    dropdownTop.value = rect.bottom + window.scrollY;
    dropdownLeft.value = rect.left + window.scrollX;
    dropdownWidth.value = rect.width;
}

function updateDropdownPosition() {
    if (permissionInputRef.value) {
        positionDropdown(permissionInputRef.value);
    }
}

onMounted(() => {
    window.addEventListener('scroll', updateDropdownPosition, true);
    window.addEventListener('resize', updateDropdownPosition);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateDropdownPosition, true);
    window.removeEventListener('resize', updateDropdownPosition);
});

// Permissions logic
function filteredPermissionOptions(): Permission[] {
    const keyword = permissionInput.value.toLowerCase();
    return form.guard_name
        ? props.permissions.filter(
              (p) =>
                  p.guard_name === form.guard_name &&
                  p.name.toLowerCase().includes(keyword) &&
                  !form.permissions.some((perm) => perm.name === p.name),
          )
        : [];
}

function handleAddPermission(name: string) {
    if (name && !form.permissions.some((perm) => perm.name === name)) {
        form.permissions.push({ name, showDropdown: false });
    }
    permissionInput.value = '';
    updateDropdownPosition();
}

function handlePermissionKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const match = filteredPermissionOptions()[0];
        if (match) {
            handleAddPermission(match.name);
        }
    }
}

function handlePermissionBlur() {
    setTimeout(() => {
        showPermissionDropdown.value = false;
    }, 100);
}

function resetForm() {
    form.reset();
    form.clearErrors();
    form.permissions = [];
    permissionInput.value = '';
    showPermissionDropdown.value = false;
}

// Modal logic
const isOpen = ref(props.isOpen);
watch(
    () => props.isOpen,
    (val) => {
        isOpen.value = val;
        if (val) open();
    },
);

function open() {
    resetForm();
    isOpen.value = true;
}

function onSubmit() {
    form.post(store.url(), {
        onSuccess: () => {
            resetForm();
            emit('close');
        },
    });
}
</script>

<template>
    <ModalForm
        :open="isOpen"
        title="modal.action.add_role"
        confirmLabel="action.create"
        :isProcessing="form.processing"
        @close="$emit('close')"
        @submit="onSubmit"
        icon="mdi:person-lock-outline"
        :variant="'default'"
        :closeOnOverlayClick="false"
    >
        <div class="space-y-3">
            <!-- Role Name -->
            <InputWithLabel
                v-model="form.name"
                label="form.label.role_name"
                placeholder="form.placeholder.role_name"
                :error="form.errors.name"
                class="placeholder:text-secondary-text"
            />
            <!-- Guard Name -->
            <div>
                <label class="text-md text-primary-text block font-semibold">
                    {{ $t('form.label.guard_name') }}
                </label>
                <Select v-model="form.guard_name">
                    <SelectTrigger class="text-primary-text w-full rounded border px-2 py-1 shadow-none focus:outline-none">
                        <SelectValue :placeholder="$t('form.placeholder.guard_name')" />
                    </SelectTrigger>
                    <SelectContent class="w-full">
                        <SelectItem
                            v-for="guard in uniqueGuards"
                            :key="guard"
                            :value="guard"
                            class="text-primary-text cursor-pointer hover:bg-gray-100"
                        >
                            {{ guard }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.guard_name && !form.guard_name" class="text-destructive fluid-text-xs-sm mt-1">
                    {{ form.errors.guard_name }}
                </p>
            </div>
            <!-- Permissions -->
            <div>
                <label class="text-md text-primary-text block font-semibold">
                    {{ $t('form.label.permission') }}
                </label>
                <div class="relative mb-2">
                    <!-- Permissions Input Section -->
                    <div ref="permissionInputRef" class="flex min-h-[42px] w-full flex-wrap items-center gap-2 rounded border px-2 py-1 text-sm">
                        <!-- Displaying selected permissions -->
                        <span
                            v-for="(perm, index) in form.permissions"
                            :key="perm.name ?? index"
                            class="flex items-center gap-1 rounded px-2 py-1 font-semibold"
                            :class="getDefaultBadgeClass(perm.name || '')"
                        >
                            {{ perm.name }}
                            <button @click="form.permissions.splice(index, 1)" class="ml-1 cursor-pointer text-xs text-black">×</button>
                        </span>

                        <!-- Permissions input field -->
                        <input
                            ref="permissionInputRef"
                            v-model="permissionInput"
                            type="text"
                            class="text-primary-text placeholder:text-secondary-muted-text min-w-[100px] grow border-none p-0 focus:outline-none"
                            :placeholder="form.permissions.length === 0 ? $t('form.placeholder.permission') : ''"
                            :disabled="!form.guard_name"
                            autocomplete="off"
                            @focus="
                                showPermissionDropdown = true;
                                updateDropdownPosition();
                            "
                            @input="updateDropdownPosition"
                            @keydown="handlePermissionKeydown"
                            @blur="handlePermissionBlur"
                            :title="!form.guard_name ? 'Please select Guard Name first.' : ''"
                        />
                    </div>

                    <p v-if="form.errors.permissions" class="text-destructive fluid-text-xs-sm mt-1">
                        {{ form.errors.permissions }}
                    </p>

                    <!-- Teleport Dropdown -->
                    <Teleport to="body">
                        <ul
                            v-if="showPermissionDropdown && filteredPermissionOptions().length"
                            class="bg-popover absolute z-50 max-h-20 overflow-y-auto rounded border text-sm shadow"
                            :style="{
                                top: dropdownTop + 'px',
                                left: dropdownLeft + 'px',
                                width: dropdownWidth + 'px',
                                position: 'absolute',
                            }"
                        >
                            <li
                                v-for="p in filteredPermissionOptions()"
                                :key="p.id"
                                class="bg-opacity-80 cursor-pointer px-3 py-2"
                                @mousedown.prevent="handleAddPermission(p.name)"
                            >
                                {{ p.name }}
                            </li>
                        </ul>
                    </Teleport>
                </div>
            </div>
        </div>
    </ModalForm>
</template>
