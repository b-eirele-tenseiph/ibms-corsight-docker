<script setup lang="ts">
import { SettingsInfoSubHeader } from '@/components/admin/settings';
import {
    FileDeleteModal,
    FileFilters,
    FileForceDeleteModal,
    FileRestoreModal,
    FileRetentionPolicy,
    FileTable,
} from '@/components/admin/settings-file';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminLayout } from '@/layouts/admin';
import { GeneralSettingsLayout, SettingsLayout } from '@/layouts/settings';
import { check, download } from '@/routes/admin/management/settings/file';
import { FileProps } from '@/types/t-general-settings';
import { router } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const props = defineProps<FileProps>();

const params = computed(() => props.filters);
const isArchivedActive = computed(() => props.filters.data === 'archived');

const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

// Handle downloading of electric meter report
const downloadFile = (filePath: string) => {
    router.post(
        check.url(),
        { file: filePath },
        {
            onSuccess: () => {
                window.location.href = download({ query: { file: filePath } }).url;
            },
            onError: (data) => {
                toast.error(data.message, {
                    classes: {
                        toast: 'border-l-3 border-l-error',
                        icon: 'text-error [&>svg]:size-4 xs:[&>svg]:size-5',
                    },
                });
            },
        },
    );
};

const modals = {
    delete: useModal<any>(),
    restore: useModal<any>(),
    forceDelete: useModal<any>(),
};

const {
    delete: { visible: showDeleteModal, open: openDeleteModal, data: showDeleteModalData, close: closeDeleteModal },
    restore: { visible: showRestoreModal, open: openRestoreModal, data: showRestoreModalData, close: closeRestoreModal },
    forceDelete: { visible: showForceDeleteModal, open: openForceDeleteModal, data: showForceDeleteData, close: closeForceDeleteModal },
} = modals;
</script>

<template>
    <AdminLayout title="head.general_settings">
        <SettingsLayout>
            <GeneralSettingsLayout>
                <SettingsInfoSubHeader title="page.file_settings.header.file" description="page.file_settings.header.file_subtitle" />

                <FileFilters :params="params" :isArchivedActive="isArchivedActive" />

                <FileRetentionPolicy :isArchivedActive="isArchivedActive" />

                <FileTable
                    :files
                    :isArchive="isArchivedActive"
                    :filters
                    @download="downloadFile"
                    @delete="openDeleteModal"
                    @restore="openRestoreModal"
                    @forceDelete="openForceDeleteModal"
                />

                <Paginate :paginate="files" />
            </GeneralSettingsLayout>
        </SettingsLayout>
    </AdminLayout>

    <FileDeleteModal :show="showDeleteModal" :file="showDeleteModalData!" @close="closeDeleteModal" />

    <FileRestoreModal :show="showRestoreModal" :file="showRestoreModalData!" @close="closeRestoreModal" />

    <FileForceDeleteModal :show="showForceDeleteModal" :file="showForceDeleteData!" @close="closeForceDeleteModal" />
</template>
