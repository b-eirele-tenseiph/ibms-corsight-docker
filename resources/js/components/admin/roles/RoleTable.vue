<script setup lang="ts">
import { TableSort } from '@/components/admin/roles';
import { DropdownAction } from '@/components/shared/dropdowns';
import StateEmpty from '@/components/shared/states/StateEmpty.vue';
import { Badge } from '@/components/ui/badge';
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import Popover from '@/components/ui/popover/Popover.vue';
import PopoverContent from '@/components/ui/popover/PopoverContent.vue';
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableEmpty, TableRow } from '@/components/ui/table';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { useRoleManagement } from '@/composables/useRolemanagement';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import { useDateFormat } from '@/lib/utils';
import type { Permission, Role } from '@/types';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import { type ComponentPublicInstance, computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';

const props = defineProps<{
    params: Record<string, any>;
    roles: Role[];
    allPermissions: Permission[];
}>();

const emit = defineEmits<{
    (e: 'delete-role', role: Role): void;
}>();

// --- Breakpoints ---
const { isExtraSmallMobile, isSmallMobile, isMobile, isTablet, isDesktop, isLaptop } = useBreakpoints();
const maxVisiblePermissions = computed(() => {
    return isExtraSmallMobile.value || isSmallMobile.value || isMobile.value ? 2 : 3;
});

// --- Role Management Logic ---
const {
    isEditing,
    editingRoleId,
    showActions,
    form: UPDATE_ROLE_FORM,
    availableGuards,
    startEditing,
    saveChanges,
    handleCancelClick,
    updatePermissionInput,
    handleInputSearch,
    filteredUpdatePermissionOptions,
    handleAddUpdatePermission,
    removePermission,
    handlePermissionBlur,
    getPermissionNameById,
} = useRoleManagement(props.allPermissions);

// --- Utilities ---
const { formatDate } = useDateFormat();

const page = usePage();

// --- Permission Input Popover Logic ---
const permissionInputContainerRef = ref<HTMLElement | null>(null);
const popoverWidth = ref(0);
const internalShowPopover = ref(false);

function syncPopoverWidth() {
    if (permissionInputContainerRef.value) {
        popoverWidth.value = permissionInputContainerRef.value.offsetWidth;
    }
}

watch(
    () => [editingRoleId.value, UPDATE_ROLE_FORM.permissions.length],
    async () => {
        await nextTick();
        syncPopoverWidth();
    },
    { immediate: true },
);

watch(
    () => updatePermissionInput.value,
    (newValue) => {
        internalShowPopover.value = newValue.length > 0 && filteredUpdatePermissionOptions.value.length > 0;
    },
    { immediate: true },
);

onMounted(() => {
    window.addEventListener('resize', syncPopoverWidth);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncPopoverWidth);
});

const handleInputFocus = () => {
    syncPopoverWidth();
    internalShowPopover.value = updatePermissionInput.value.length > 0;
};

const handleInputBlur = () => {
    setTimeout(() => {
        handlePermissionBlur();
        internalShowPopover.value = false;
    }, 150);
};

const heights = reactive<Record<number, number>>({});
const contentRefs = ref<Record<number, HTMLElement | null>>({});
const observers: Record<number, ResizeObserver> = {};

function setContentRef(id: number) {
    return (el: Element | ComponentPublicInstance | null) => {
        const htmlEl = el instanceof HTMLElement ? el : el && '$el' in el ? ((el as any).$el as HTMLElement) : null;
        contentRefs.value[id] = htmlEl;
        if (htmlEl) {
            if (observers[id]) observers[id].disconnect();
            observers[id] = new ResizeObserver((entries) => {
                for (const entry of entries) {
                    if (entry.contentRect) {
                        heights[id] = Math.round(entry.contentRect.height);
                    }
                }
            });
            observers[id].observe(htmlEl);
        }
    };
}

onBeforeUnmount(() => {
    Object.values(observers).forEach((observer) => observer.disconnect());
});

onBeforeUnmount(() => {
    Object.values(observers).forEach((o) => o.disconnect());
});

// --- Click Outside Logic ---
function handleClickOutside(event: MouseEvent) {
    const target = event.target as HTMLElement;
    const isInDropdown = Object.values(contentRefs.value).some((el) => el?.contains(target));
    const isInShadcnSelect = target.closest('.radix-select-content');
    const isInEditing = editingRoleId.value !== null;

    if (isInDropdown || isInShadcnSelect || isInEditing) return;

    showActions.value = null;
    editingRoleId.value = null;
    isEditing.value = false;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));

// --- Table Data for Header Loop ---
const tableData = computed(() => {
    const isSmallScreen = isTablet.value || isMobile.value || isSmallMobile.value;
    return props.roles.length > 0 || isSmallScreen ? props.roles : [{}];
});

// --- Expanded Roles ---
const expandedRoles = reactive<Record<number, boolean>>({});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(role, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableSort :data="params" :header="{ label: $t('table.header.role_name'), by: 'name' }" />
                            <TableSort :data="params" :header="{ label: $t('table.header.guard_name'), by: 'guard_name' }" />
                            <TableHead
                                class="h-12 w-1/4 place-content-center py-3 select-none"
                                :style="isTablet || isMobile || isSmallMobile ? { height: 17 + heights[(role as Role).id] + 'px' } : {}"
                            >
                                {{ $t('table.header.permissions') }}
                            </TableHead>
                            <TableSort
                                :data="params"
                                :header="{
                                    label: $t('table.header.last_updated'),
                                    by: 'updated_at',
                                }"
                            />
                            <TableHead class="h-12 py-3" />
                        </TableRow>
                    </template>
                </TableHeader>
                <TableBody v-if="roles.length > 0" class="table-body [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="role in roles" :key="role.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3">
                                <template v-if="editingRoleId === role.id">
                                    <div class="flex flex-col gap-1">
                                        <input
                                            v-model="UPDATE_ROLE_FORM.name"
                                            type="text"
                                            placeholder="Enter role name"
                                            class="w-[90px] rounded border px-2 py-1 text-sm"
                                            @click.stop
                                        />
                                        <div v-if="page.props.errors.name" class="text-destructive text-xs">
                                            {{ page.props.errors.name }}
                                        </div>
                                    </div>
                                </template>

                                <template v-else-if="role.name">
                                    <div>{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</div>
                                </template>

                                <template v-else>
                                    <div class="text-secondary-text italic">–</div>
                                </template>
                            </TableCell>

                            <TableCell :class="[isExtraSmallMobile || isSmallMobile || isMobile || isTablet ? 'flex' : '', 'h-12 py-3']">
                                <template v-if="isEditing && editingRoleId === role.id">
                                    <div :class="[isExtraSmallMobile || isSmallMobile || isMobile || isTablet ? 'self-center' : '', 'relative z-10']">
                                        <Select v-model="UPDATE_ROLE_FORM.guard_name">
                                            <SelectTrigger class="rounded px-2 py-1 text-sm focus:outline-none">
                                                <SelectValue placeholder="Select Guard" />
                                            </SelectTrigger>
                                            <SelectContent class="w-full">
                                                <SelectItem
                                                    v-for="guard in availableGuards"
                                                    :key="guard"
                                                    :value="guard"
                                                    class="text-primary-text hover:bg-gray-100"
                                                >
                                                    {{ guard }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="px-2 py-1">{{ role.guard_name }}</div>
                                </template>
                            </TableCell>

                            <TableCell
                                :ref="setContentRef(role.id)"
                                :class="[isLaptop || isDesktop ? 'w-[420px]' : 'w-full', '!h-auto overflow-visible py-2 align-top']"
                            >
                                <template v-if="editingRoleId === role.id">
                                    <div @click.stop class="relative mb-2">
                                        <div
                                            ref="permissionInputContainerRef"
                                            class="flex flex-wrap items-center gap-2 rounded border px-2 py-1 text-sm"
                                        >
                                            <span
                                                v-for="id in UPDATE_ROLE_FORM.permissions"
                                                :key="id"
                                                class="rounded-sm px-2.5 py-0.5 text-xs font-medium"
                                                :class="getDefaultBadgeClass(getPermissionNameById(id))"
                                            >
                                                {{ getPermissionNameById(id) }}
                                                <button
                                                    @click="removePermission(id)"
                                                    class="text-primary-text dark:text-background ml-1 cursor-pointer text-xs"
                                                >
                                                    ×
                                                </button>
                                            </span>
                                            <Popover :open="internalShowPopover">
                                                <PopoverTrigger as-child>
                                                    <input
                                                        variant="ghost"
                                                        v-model="updatePermissionInput"
                                                        @input="handleInputSearch"
                                                        type="text"
                                                        class="placeholder:text-secondary-text z-10 grow border-none focus:outline-none"
                                                        :placeholder="UPDATE_ROLE_FORM.permissions.length === 0 ? 'Type to search permissions' : ''"
                                                        :disabled="!UPDATE_ROLE_FORM.guard_name"
                                                        @blur="handleInputBlur"
                                                        @focus="handleInputFocus"
                                                    />
                                                </PopoverTrigger>

                                                <PopoverContent
                                                    class="bg-popover z-50 rounded border p-2 text-sm shadow-md"
                                                    :style="{ width: popoverWidth + 'px' }"
                                                    :align-offset="-10"
                                                    align="start"
                                                >
                                                    <ul v-if="filteredUpdatePermissionOptions.length">
                                                        <li
                                                            v-for="p in filteredUpdatePermissionOptions"
                                                            :key="p.id"
                                                            class="hover:bg-opacity-100 cursor-pointer px-3 py-2"
                                                            @mousedown.prevent="handleAddUpdatePermission(p.id)"
                                                        >
                                                            {{ p.name }}
                                                        </li>
                                                    </ul>
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                        <div v-if="page.props.errors.permissions" class="text-destructive mt-1 text-xs">
                                            {{ page.props.errors.permissions }}
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div :class="['flex flex-wrap gap-2', role.permissions.length < 3 ? 'self-center' : '']">
                                        <template v-if="!expandedRoles[role.id]">
                                            <Badge
                                                v-for="(permission, index) in role.permissions.slice(0, maxVisiblePermissions)"
                                                :key="index"
                                                class="font-semibold"
                                                :class="getDefaultBadgeClass(permission.name)"
                                            >
                                                {{ permission.name }}
                                            </Badge>
                                            <button
                                                v-if="role.permissions.length > maxVisiblePermissions"
                                                @click="expandedRoles[role.id] = true"
                                                class="text-primary cursor-pointer text-xs underline"
                                            >
                                                {{ $t('action.show_more') }}
                                            </button>
                                        </template>
                                        <template v-else>
                                            <Badge
                                                v-for="(permission, index) in role.permissions"
                                                :key="index"
                                                class="font-semibold"
                                                :class="getDefaultBadgeClass(permission.name)"
                                            >
                                                {{ permission.name }}
                                            </Badge>
                                            <button @click="expandedRoles[role.id] = false" class="text-primary cursor-pointer text-xs underline">
                                                {{ $t('action.show_less') }}
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </TableCell>

                            <TableCell class="h-12 py-2">
                                {{ formatDate(role.updated_at) }}
                            </TableCell>

                            <TableCell class="h-12 py-2">
                                <DropdownAction
                                    v-if="showActions !== role.id && editingRoleId !== role.id"
                                    class="h-full cursor-pointer"
                                    :actions="{
                                        update: { label: 'modal.action.update_role' },
                                        delete: { label: 'modal.action.delete_role' },
                                    }"
                                    @update="startEditing(role)"
                                    @delete="emit('delete-role', role)"
                                />
                                <DropdownAction v-else class="h-full cursor-pointer">
                                    <template #extension>
                                        <DropdownMenuItem @click="saveChanges(role)">
                                            <Icon icon="ri:save-line" class="text-primary-text h-4 w-4" />
                                            {{ $t('action.save') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="handleCancelClick()">
                                            <Icon icon="mdi:cancel-bold" class="text-primary-text h-4 w-4" />
                                            {{ $t('action.cancel') }}
                                        </DropdownMenuItem>
                                    </template>
                                </DropdownAction>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>

                <TableBody v-else>
                    <TableEmpty :colspan="5">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
