import { update } from '@/routes/admin/management/settings/roles';
import type { Permission, Role } from '@/types/t-role';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ComputedRef, ref, watch } from 'vue';

export function useRoleManagement(permissions: Permission[]) {
    // Edit State
    const isEditing = ref(false);
    const editingRoleId = ref<number | null>(null);
    const showActions = ref<number | null>(null);

    // Add/Edit Form
    const form = useForm<{
        name: string | null;
        guard_name: string | null;
        permissions: number[];
    }>({
        name: null,
        guard_name: null,
        permissions: [],
    });

    // Available guards
    const availableGuards = computed(() => {
        return Array.from(new Set(permissions.map((p) => p.guard_name)));
    });

    // Watch for guard_name changes to reset permissions
    watch(
        () => form.guard_name,
        (newGuard, oldGuard) => {
            if (oldGuard && oldGuard !== newGuard && form.permissions.length > 0) {
                form.permissions = [];
            }
        },
    );

    // Start editing
    const startEditing = (role: Role) => {
        editingRoleId.value = role.id;
        isEditing.value = true;
        form.name = role.name;
        form.guard_name = role.guard_name;
        form.permissions = role.permissions.map((p) => p.id);
        updatePermissionInput.value = '';
    };

    const handleCancelClick = () => {
        editingRoleId.value = null;
        isEditing.value = false;
        showActions.value = null;
        form.reset();
        router.reload({ only: [] });
    };

    const saveChanges = (role: Role) => {
        form.put(update.url({ role: role.id }), {
            onSuccess: () => {
                form.reset();
                form.clearErrors();
                editingRoleId.value = null;
                isEditing.value = false;
                showActions.value = null;
            },
            onError: (error) => {
                console.error('Validation failed:', error);
            },
        });
    };

    // Permission dropdown logic
    const updatePermissionInput = ref('');
    const showUpdatePermissionDropdown = ref(false);

    const handleInputSearch = () => {
        showUpdatePermissionDropdown.value = !!updatePermissionInput.value;
    };

    const filteredUpdatePermissionOptions: ComputedRef<Permission[]> = computed(() => {
        const keyword = updatePermissionInput.value.toLowerCase();
        return permissions.filter(
            (p) => p.guard_name === form.guard_name && p.name.toLowerCase().includes(keyword) && !form.permissions.includes(p.id),
        );
    });

    const handleAddUpdatePermission = (id: number) => {
        if (!form.permissions.includes(id)) {
            form.permissions.push(id);
        }
        updatePermissionInput.value = '';
    };

    const removePermission = (id: number) => {
        form.permissions = form.permissions.filter((pid) => pid !== id);
    };

    const getPermissionNameById = (id: number): string => {
        return permissions.find((p) => p.id === id)?.name || 'Unknown';
    };

    const handlePermissionBlur = () => {
        setTimeout(() => {
            showUpdatePermissionDropdown.value = false;
        }, 100);
    };

    // Dropdown positioning (optional)
    const dropdownTop = ref(0);
    const dropdownLeft = ref(0);
    const dropdownWidth = ref(0);
    const permissionInputRef = ref<HTMLElement | null>(null);

    const updateDropdownPosition = () => {
        if (permissionInputRef.value) {
            const rect = permissionInputRef.value.getBoundingClientRect();
            dropdownTop.value = rect.bottom + window.scrollY;
            dropdownLeft.value = rect.left + window.scrollX;
            dropdownWidth.value = rect.width;
        }
    };

    return {
        // State
        isEditing,
        editingRoleId,
        showActions,

        // Form
        form,
        availableGuards,
        startEditing,
        saveChanges,
        handleCancelClick,

        // Permission Tag Input
        updatePermissionInput,
        showUpdatePermissionDropdown,
        handleInputSearch,
        filteredUpdatePermissionOptions,
        handleAddUpdatePermission,
        removePermission,
        getPermissionNameById,
        handlePermissionBlur,
        updateDropdownPosition,
        dropdownTop,
        dropdownLeft,
        dropdownWidth,
        permissionInputRef,
    };
}
